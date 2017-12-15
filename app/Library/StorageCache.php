<?php

namespace App\Library;

use Illuminate\Support\Facades\Storage;

class StorageCache
{
    protected $storage;

    public function __construct($disk_env)
    {
        // NOTE: Storage Disk will default in config/filesystems.php
        try {
            $this->storage = Storage::disk($disk_env);
            if (is_null($this->storage)) {
            }
        } catch (\Exception $e) {
            throw new \Exception("$disk_env => Cache Storage ERROR: $e");
        }
    }

    public function CheckCachedData($file_name)
    {
        //TODO remove else statement not needed only for visual console logs
        $cache_check = false;
        if ($this->storage->exists($file_name)) {
            $cache_check = true;
        }
        return $cache_check;
    }

    public function cacheContent($file_name, $content)
    {
        $this->storage->put($file_name, $content);
    }

    public function getCacheData($file_name)
    {
        // TODO if more fields exists (ex: time to live) then notify that cache is STALE
        // if Cache exists then set cached data
        if ($this->storage->exists($file_name)) {
            return $this->storage->get($file_name);
        } else {
            return null;
        }
    }

    public function removeCachedData($file_name)
    {
        if ($this->storage->exists($file_name)) {
            if ($file_name === '/' || empty($file_name)) {
                throw new \Exception("Cache Storage ERROR: DO NOT DELETE ROOT '/' OR file_name was empty");
            }
            $this->storage->delete($file_name);
        }
    }
}
