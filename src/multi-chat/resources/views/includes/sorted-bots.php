<?php

function sortBotsByDate($bots)
{
    /*
        * Sort the bots according to the creation date (most recent first).
        */
    $bots = $bots->sortByDesc('created_at'); // Assuming 'created_at' is the timestamp field
    return $bots;
}
function sortBotsByModel($bots)
{
    /*
        * Sort the bots according to the model order specified in the
        * database, prioritizing the creation date (most recent first) for
        * bots with the same model order.
        */
    $bots = $bots
        ->sortBy('order')
        ->groupBy('order')
        ->map(function ($subSet) {
            return sortBotsByDate($subSet);
        })->collapse();
    return $bots;
}
function sortBotsByName($bots)
{
    /*
        * Sort the bots according to their name (a-z).
        */
    $bots = $bots->sortBy('name');
    return $bots;
}
function sortBotsByNameDesc($bots)
{
    /*
        * Sort the bots according to their name (z-a).
        */
    $bots = $bots->sortByDesc('name');
    return $bots;
}
function sortUserBots($bots, $sortingFunc = 'sortBotsByModel')
{
    /*
        * Prioritize sorting the user's bot over other bots.
        */

    $userId = request()->user()->id;
    // Filter and sort the bots owned by the current user
    $userBots = $bots
        ->filter(function ($bot) use ($userId) {
            return $bot->owner_id == $userId;
        });
    $userBots = $sortingFunc($userBots);

    // Filter the remaining bots and sorting them
    $otherBots = $bots
        ->filter(function ($bot) use ($userId) {
            return $bot->owner_id != $userId;
        });
    $otherBots = $sortingFunc($otherBots);

    // Merge the sorted user bots with the randomized other bots
    return $userBots->merge($otherBots)->values();
}
function addIndexProperty($arr_of_objs, $key) {
    /*
     * Add the index as a property to each item in the array of objects.
     */
    $prop_name = "{$key}-order-index";
    $result = $arr_of_objs->map(function (object $item, int $index) use ($prop_name) {
        $item->$prop_name = $index;
        return $item;
    });
    return $result;
}

function initBotIndexes($bots) {
    $bot_sorting_methods = getBotSortingMethods();
    foreach ($bot_sorting_methods as $method) {
        $bots = sortUserBots($bots, $method["sorting_method"]);
        $bots = addIndexProperty($bots, $method["index_key"]);
    }
    $bots = sortUserBots($bots, $bot_sorting_methods[0]["sorting_method"]);

    return $bots;
}

function getBotSortingMethods(){
    return [
        // The default sorting method
        [
            "index_key" => "model",
            "sorting_method" => "sortBotsByModel",
            "name" => "room.sort_by.model"
        ],
        
        // Other sorting method
        [
            "index_key" => "date",
            "sorting_method" => "sortBotsByDate",
            "name" => "room.sort_by.date"
        ],
        [
            "index_key" => "name",
            "sorting_method" => "sortBotsByName",
            "name" => "room.sort_by.name"
        ],
        [
            "index_key" => "name-desc",
            "sorting_method" => "sortBotsByNameDesc",
            "name" => "room.sort_by.name_desc"
        ],
    ];
}

function getSortedBots() {
    $bots = App\Models\Bots::Join('llms', function ($join) {
        $join->on('llms.id', '=', 'bots.model_id');
    })
        ->leftjoin('users', 'users.id', '=', 'bots.owner_id')
        ->where('llms.enabled', '=', true)
        ->wherein(
            'bots.model_id',
            DB::table('group_permissions')
                ->join('permissions', 'group_permissions.perm_id', '=', 'permissions.id')
                ->select(DB::raw('substring(permissions.name, 7) as model_id'), 'perm_id')
                ->where('group_permissions.group_id', Auth::user()->group_id)
                ->where('permissions.name', 'like', 'model_%')
                ->get()
                ->pluck('model_id'),
        )
        ->where(function ($query) {
            $query
                ->where('bots.visibility', '=', 0)->orwhere('bots.visibility', '=', 1)
                ->orWhere(function ($query) {
                    $query->where('bots.visibility', '=', 3)->where('bots.owner_id', '=', Auth::user()->id);
                })
                ->orWhere(function ($query) {
                    $query
                        ->where('bots.visibility', '=', 2)
                        ->where('users.group_id', '=', Auth::user()->group_id);
                });
        })
        ->select(
            'llms.*',
            'bots.*',
            DB::raw('COALESCE(bots.description, llms.description) as description'),
            DB::raw('COALESCE(bots.config, llms.config) as config'),
            DB::raw('COALESCE(bots.image, llms.image) as image'),
            'llms.name as llm_name',
        )
        ->orderby('llms.order')
        ->orderby('bots.created_at')
        ->get();
    
    $bots = initBotIndexes($bots);
    return $bots;
}

?>