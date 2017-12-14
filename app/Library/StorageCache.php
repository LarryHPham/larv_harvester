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
                print("ERROR STORAGE NOT FOUND\n");
            } else {
                print("Storage Found: ".$disk_env."\n");
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
            print("CACHE FILE => CheckCachedData => ALREADY EXISTS => $file_name\n");
        } else {
            print("CACHE FILE => CheckCachedData =>  DOES NOT EXISTS => $file_name\n");
        }
        return $cache_check;
    }

    public function cacheContent($file_name, $content)
    {
        // TODO if more fields exists (ex: time to live) then recache data point
        $this->storage->put($file_name, $content);
        print("CACHE FILE => cacheContent => CREATED => $file_name\n");
    }

    public function getCacheData($file_name)
    {
        // TODO if more fields exists (ex: time to live) then notify that cache is STALE
        // if Cache exists then set cached data
        if ($this->storage->exists($file_name)) {
            print("CACHE FILE => getCacheData => GRABBING => $file_name\n");
            return $this->storage->get($file_name);
        } else {
            print("CACHE FILE => getCacheData => DOES NOT EXIST => $file_name\n");
            return null;
        }
    }

    public function removeCachedData($file_name)
    {
        print("CACHE FILE => removeCachedData => DELETE => $file_name\n");
        if ($this->storage->exists($file_name)) {
            if ($file_name === '/' || empty($file_name)) {
                throw new \Exception("Cache Storage ERROR: DO NOT DELETE ROOT '/' OR file_name was empty");
            }
            $this->storage->delete($file_name);
        }
        print("CACHE FILE => DELETED => $file_name\n");
    }
}
