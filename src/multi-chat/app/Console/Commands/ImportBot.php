<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\BotController;
use App\Models\LLMs;
use App\Models\Bots;

class ImportBot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:import {botfile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import bot from a botfile.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $botfilePath = $this->argument('botfile');
        $botfileContents = file_get_contents($botfilePath);
        $botfile = $this->parseBotfile($botfileContents);
        
        $model = LLMs::where('name', '=', $botfile['base'])->first();

        if (!$model) {
            print('The selected model does not exist.'."\n");
            return;
        }
        $model_id = $model->id;
        $visibility = 0; // System bot
        

        $bot = Bots::where('name', '=', $botfile['name'])->first();
        if ($bot) {
            print('The bot "'.$botfile['name']. '" already exists. Skipped.'."\n");
            return;
        }

        $bot = new Bots();
        $config = [];
        $botController = new BotController();
        $config['modelfile'] = $botController->modelfile_parse($botfile['modelfile']);
        $config['react_btn'] = ["feedback", "translate", "quote", "other"];
        $config = json_encode($config);
        $bot->fill([
            'name' => $botfile['name'],
            'type' => 'prompt',
            'visibility' => $visibility,
            'description' => $botfile['description'],
            'model_id' => $model_id,
            'config' => $config
        ]);
        if (isset($botfile['avatar'])) {
            $bot->image = $botfile['avatar'];
        }
        $bot->save();

        print('Bot "'.$botfile['name']. '" imported successfully.'."\n");
    
    }

    private function getExtension($contentType) {
        $mimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
        ];

        // Search for the content type in the array
        if (isset($mimeTypes[$contentType])) {
            return $mimeTypes[$contentType];
        }

        // If not found, try to extract extension from content-type using explode
        $parts = explode('/', $contentType);
        if (count($parts) === 2) {
            return $parts[1];
        }

        // If all else fails, return null
        return null;
    }

    private function storeImage($base64EncodedContents, $contentType)
    {
        $imageData = base64_decode($base64EncodedContents);
        $imageName = Str::random(40) . '.' . $this->getExtension($contentType);
        $path = 'public/images/' . $imageName;

        Storage::put($path, $imageData);

        return $path;
    }

    private function parseBotfile($rawContent)
    {
        $botfile = $this->parseHttpRequest($rawContent);
        $botfile = $this->parseMultipartRequest($botfile['body'], $botfile['headers']['content-type']);
        $parsedBotfile = $this->parseKuwaModelfile($botfile[0]['body']);
        $botAvatarPart = array_values(array_filter($botfile, function ($v, $k) {
            // Check if 'content-location' key exists and its value is '/bot-avatar'
            return isset($v['headers']['content-location']) && $v['headers']['content-location'] === '/bot-avatar';
        }, ARRAY_FILTER_USE_BOTH));
        if (count($botAvatarPart) > 0) {
            $avatarPath = $this->storeImage($botAvatarPart[0]['body'], $botAvatarPart[0]['headers']['content-type']);
            $parsedBotfile['avatar'] = $avatarPath;
        }

        return $parsedBotfile;
    }

    private function parseKuwaModelfile($rawContent)
    {
        $kuwaModelfile = [
            'version' => null,
            'name' => null,
            'description' => null,
            'base' => null,
            'modelfile' => ''
        ];
        $lines = explode("\n", $rawContent);

        foreach ($lines as $line) {
            if (strpos($line, 'KUWABOT version ') === 0) {
                $kuwaModelfile['version'] = trim(str_replace('KUWABOT version ', '', $line), '"');
            } elseif (strpos($line, 'KUWABOT name ') === 0) {
                $kuwaModelfile['name'] = trim(str_replace('KUWABOT name ', '', $line), '"');
            } elseif (strpos($line, 'KUWABOT description ') === 0) {
                $kuwaModelfile['description'] = trim(str_replace('KUWABOT description ', '', $line), '"');
            } elseif (strpos($line, 'KUWABOT base ') === 0) {
                $kuwaModelfile['base'] = trim(str_replace('KUWABOT base ', '', $line), '"');
            } else {
                $kuwaModelfile['modelfile'] .= $line . "\n";
            }
        }

        $kuwaModelfile['modelfile'] = trim($kuwaModelfile['modelfile']);

        return $kuwaModelfile;
    }

    private function parseHttpRequest($rawHttpRequest)
    {
        $parsedHttpRequest = [
            'headers' => [],
            'body' => '',
        ];

        // Split headers and body
        list($headerLines, $parsedHttpRequest['body']) = explode("\r\n\r\n", $rawHttpRequest, 2);

        // Parse headers
        $headerLines = explode("\r\n", $headerLines);
        foreach ($headerLines as $headerLine) {
            if (!empty($headerLine)) {
                list($headerName, $headerValue) = explode(": ", $headerLine, 2);
                $parsedHttpRequest['headers'][strtolower($headerName)] = trim($headerValue);
            }
        }
        return $parsedHttpRequest;
    }

    private function parseMultipartRequest($requestBody, $contentType)
    {
        // Validate input
        if (empty($requestBody) || empty($contentType)) {
            throw new \InvalidArgumentException('Request body and boundary are required.');
        }
        $parts = [];

        preg_match('/boundary=(?:"([^"]+)"|([^;]+))/i', $contentType, $matches);
        $boundary = $matches[1];
        $bodyParts = explode("--" . $boundary, $requestBody);

        // Remove preamble and epilogue
        array_shift($bodyParts);
        array_pop($bodyParts);

        foreach ($bodyParts as $bodyPart) {
            $parts[] = $this->parseHttpRequest($bodyPart);
        }

        return $parts;
    }
}
