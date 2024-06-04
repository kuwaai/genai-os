<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Http\Requests\ChatRequest;
use Illuminate\Http\Request;
use App\Models\Histories;
use App\Jobs\ImportChat;
use App\Jobs\RequestChat;
use App\Models\Chats;
use App\Models\LLMs;
use App\Models\Bots;
use App\Models\User;
use App\Models\Feedback;
use DB;
use Session;

class BotController extends Controller
{
    function modelfile_parse($data)
    {
        $commands = [];
        $currentCommand = [
            'name' => '',
            'args' => '',
        ];
        $flags = [
            'system' => false,
            'beforePrompt' => false,
            'afterPrompt' => false,
        ];

        // Split the input data into lines
        $lines = preg_split('/\r\n|\r|\n/', trim($data));

        // Iterate over each line
        foreach ($lines as $line) {
            $line = trim($line);

            // Array of command keywords
            $commandKeywords = ['FROM', 'ADAPTER', 'LICENSE', 'TEMPLATE', 'SYSTEM', 'PARAMETER', 'MESSAGE', 'BEFORE-PROMPT', 'AFTER-PROMPT'];

            // Check if the line starts with a command keyword
            if (strpos($line, '#') === 0) {
                // If a command is already being accumulated, push it to the commands array
                if ($currentCommand['name'] !== '') {
                    $commands[] = $currentCommand;
                }
                $currentCommand = [
                    'name' => $line,
                    'args' => '',
                ];
            } elseif (
                array_reduce(
                    $commandKeywords,
                    function ($carry, $keyword) use ($line) {
                        return $carry || stripos($line, $keyword) === 0;
                    },
                    false,
                )
            ) {
                // If a command is already being accumulated, push it to the commands array
                if ($currentCommand['name'] !== '') {
                    $commands[] = $currentCommand;
                }

                // Start a new command
                $currentCommand = [
                    'name' => '',
                    'args' => '',
                ];

                // Split the line into command type and arguments
                if (preg_match('/^(\S+)\s*(.*)$/', $line, $matches)) {
                    $commandType = $matches[1];
                    $commandArgs = isset($matches[2]) ? $matches[2] : '';
                } else {
                    $commandType = $line;
                    $commandArgs = '';
                }

                // Set the current command's name and arguments
                $currentCommand['name'] = strtolower($commandType);
                $currentCommand['args'] = trim($commandArgs);

                if (($currentCommand['name'] === 'system' && $flags['system']) || ($currentCommand['name'] === 'before-prompt' && $flags['beforePrompt']) || ($currentCommand['name'] === 'after-prompt' && $flags['afterPrompt'])) {
                    $currentCommand = [
                        'name' => '',
                        'args' => '',
                    ];
                } else {
                    // Set the flag for the current command
                    $flags[$currentCommand['name']] = true;
                }
            } else {
                // If the line does not start with a command keyword, append it to the current command's arguments
                if (strpos($currentCommand['name'], '#') === 0 || (strlen($currentCommand['args']) > 6 && substr($currentCommand['args'], -3) === '"""' && substr($currentCommand['args'], 0, 3) === '"""')) {
                    $commands[] = $currentCommand;
                    // Start a new command
                    $currentCommand = [
                        'name' => '',
                        'args' => '',
                    ];
                    if (preg_match('/^(\S+)\s*(.*)$/', $line, $matches)) {
                        $commandType = $matches[1];
                        $commandArgs = isset($matches[2]) ? $matches[2] : '';
                    } else {
                        $commandType = $line;
                        $commandArgs = '';
                    }
                    $currentCommand['name'] = strtolower($commandType);
                    $currentCommand['args'] = trim($commandArgs);
                    if ($line === '') {
                        $commands[] = $currentCommand;
                    }
                } elseif ($line === '' && $currentCommand['name'] === '' && $currentCommand['args'] === '') {
                    $commands[] = [
                        'name' => '',
                        'args' => '',
                    ];
                } elseif ($currentCommand['name'] !== '') {
                    $currentCommand['args'] .= "\n" . $line;
                }
            }
        }

        // Push the last command to the commands array
        if ($currentCommand['name'] !== '') {
            $commands[] = $currentCommand;
        }
        return $commands;
    }

    public function home(Request $request)
    {
        return view('store');
    }
    public function create(Request $request)
    {
        $model_id = LLMs::where('name', '=', $request->input('llm_name'))->first()->id;
        if ($model_id) {
            $bot = new Bots();
            $config = [];
            if ($request->input('modelfile')) {
                $config['modelfile'] = $this->modelfile_parse($request->input('modelfile'));
            }
            if ($request->input('react_btn')) {
                $config['react_btn'] = $request->input('react_btn');
            }
            $config = json_encode($config);
            $bot->fill(['name' => $request->input('bot-name'), 'type' => 'prompt', 'visibility' => 1, 'description' => $request->input('bot-describe'), 'owner_id' => $request->user()->id, 'model_id' => $model_id, 'config' => $config]);
            $bot->save();
        }

        return redirect()->route('store.home');
    }
    public function update(Request $request)
    {
        $bot = Bots::findOrFail($request->input('id'));
        $model_id = LLMs::where('name', '=', $request->input('llm_name'))->first()->id;

        $config = [];
        if ($request->input('modelfile')) {
            $config['modelfile'] = $this->modelfile_parse($request->input('modelfile'));
        }
        if ($request->input('react_btn')) {
            $config['react_btn'] = $request->input('react_btn');
        }
        $config = json_encode($config);
        if ($request->input('bot-name')) {
            $bot->name = $request->input('bot-name');
        }
        if ($request->input('bot-describe')) {
            $bot->description = $request->input('bot-describe');
        }
        $bot->model_id = $model_id;
        $bot->config = $config;
        $bot->save();
        return redirect()->route('store.home');
    }
    public function delete(Request $request): RedirectResponse
    {
        $bot = Bots::findOrFail($request->input('id'));
        if ($bot->image) {
            Storage::delete($bot->image);
        }
        $bot->delete();
        return Redirect::route('store.home');
    }
}
