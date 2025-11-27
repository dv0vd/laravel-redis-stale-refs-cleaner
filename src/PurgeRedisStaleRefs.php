<?php

declare(strict_types=1);

namespace Dv0vD\LaravelRedisStaleRefsCleaner;

use Illuminate\Cache\RedisStore;
use Illuminate\Console\Command;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class PurgeRedisStaleRefs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:purge-stale-refs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes stale refs in Redis.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('Starting purge of stale cache references', ['command' => $this->getName()]);

        $store = Cache::store()->getStore();
        if (!($store instanceof RedisStore)) {
            $logMessage = 'Oops! Your cache store is not Redis. Nothing to purge here.';
            Log::warning($logMessage, ['command' => $this->getName()]);
            $this->error($logMessage);
            return Command::FAILURE;
        }

        $cachePrefix = Cache::getPrefix();
        $dbPrefix = config('database.redis.options.prefix') ?? '';

        /** @var PhpRedisConnection */
        $redis = Redis::connection('cache');

        // Select all temporary and permanent reference sets
        $standardRefs = $redis->keys("{$cachePrefix}*:standard_ref");
        $foreverRefs = $redis->keys("{$cachePrefix}*:forever_ref");
        $refs = array_merge($standardRefs, $foreverRefs);

        $purged = 0;
        $i = 0;

        foreach ($refs as $refKey) {
            // Remove database prefix
            $refKey = str_replace($dbPrefix, '', $refKey) ?? '';
            $iterator = null;

            do {
                $result = $redis->sScan($refKey, $iterator, ['count' => 1000]);
                if (!$result) {
                    break;
                }

                // Laravel 6–7: keys array
                // Laravel 8+ format: [iterator, members]
                if (isset($result[1])) {
                    [$iterator, $members] = $result;
                } else {
                    $members = $result;
                    $iterator = 0;
                }

                foreach ($members as $member) {
                    if (!$redis->exists($member)) {
                        $redis->sRem($refKey, $member);
                        $purged++;
                    }
                }

                $i++;
                $logMessage = "Iteration: $i. Keys purged: $purged";
                $this->info($logMessage);
                Log::info($logMessage, ['command' => $this->getName()]);
            } while ($iterator > 0);
        }

        $logMessage = 'Stale cache references purge completed successfully';
        $this->info($logMessage);
        Log::info($logMessage, ['command' => $this->getName()]);

        return Command::SUCCESS;
    }
}
