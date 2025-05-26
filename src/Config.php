<?php

declare(strict_types=1);

namespace Velolia\Config;

use Velolia\Config\Exception\ConfigDirectoryNotFoundException;

class Config
{
    protected const CONFIG_DIR_NAME = 'config';
    protected static array $cache = [];
    protected static string $configPath = '';
    protected static bool $initialized = false;

    /**
     * Set manual path for config directory
     */
    public static function setConfigPath(string $path): void
    {
        self::$configPath = rtrim($path, '/\\');
        self::$initialized = true;
        self::$cache = [];
    }

    /**
     * Get config value
     * 
     * @throws ConfigDirectoryNotFoundException
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::initializeIfNeeded();
        
        [$filename, $keys] = self::parseKey($key);
        $config = self::loadConfig($filename);
        
        return self::extractValue($config, $keys, $default);
    }

    /**
     * Set config value
     * 
     * @throws ConfigDirectoryNotFoundException
     */
    public static function set(string $key, mixed $value): mixed
    {
        self::initializeIfNeeded();

        [$filename, $keys] = self::parseKey($key);
        $config = self::loadConfig($filename);
        
        self::setValue($config, $keys, $value);
        self::$cache[$filename] = $config;
        
        return $value;
    }

    /**
     * Parse key to filename and array keys
     */
    protected static function parseKey(string $key): array
    {
        $parts = explode('.', $key);
        $filename = array_shift($parts);
        
        return [$filename, $parts];
    }

    /**
     * Load config from file or cache
     */
    protected static function loadConfig(string $filename): array
    {
        if (!isset(self::$cache[$filename])) {
            $filePath = self::$configPath . DIRECTORY_SEPARATOR . $filename . '.php';
            self::$cache[$filename] = file_exists($filePath) ? require $filePath : [];
        }

        return self::$cache[$filename];
    }

    /**
     * Extract value from nested array with array keys
     */
    protected static function extractValue(array $array, array $keys, mixed $default): mixed
    {
        $value = $array;
        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return $default;
            }
            $value = $value[$key];
        }
        return $value;
    }

    /**
     * Set value in nested array with array keys
     */
    protected static function setValue(array &$array, array $keys, mixed $value): void
    {
        $current = &$array;
        foreach ($keys as $key) {
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }
        $current = $value;
    }

    /**
     * Initialize config path if not initialized
     *
     * @throws ConfigDirectoryNotFoundException
     */
    protected static function initializeIfNeeded(): void
    {
        if (self::$initialized) {
            return;
        }

        $dir = getcwd();
        
        while ($dir && is_dir($dir) && $dir !== dirname($dir)) {
            $configPath = $dir . DIRECTORY_SEPARATOR . self::CONFIG_DIR_NAME;
            if (is_dir($configPath)) {
                self::setConfigPath($configPath);
                return;
            }
            $dir = dirname($dir);
        }

        throw new ConfigDirectoryNotFoundException('Config directory not found. Please set config path manually using setConfigPath()');
    }
}
