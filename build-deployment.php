<?php
/**
 * Deployment Package Builder
 *
 * This script creates a clean deployment package for the PreProduct plugin
 * excluding development files and directories.
 *
 * @package PreProduct
 * @since 1.0.0
 */

class DeploymentBuilder
{
    private $source_dir;
    private $build_dir;
    private $plugin_name = 'woo-preproduct';
    
    // Files and directories to exclude from deployment
    private $exclude_patterns = [
        '.git',
        '.gitignore',
        '.github',
        'node_modules',
        'build-deployment.php',
        'package.json',
        'package-lock.json',
        'composer.json',
        'composer.lock',
        'vendor',
        'tests',
        '.DS_Store',
        'Thumbs.db',
        '*.log',
        '.taskmaster',
        '.claude',
        '.mcp.json',
        'CLAUDE.md'
    ];

    public function __construct($source_dir = null)
    {
        $this->source_dir = $source_dir ?: __DIR__;
        $this->build_dir = $this->source_dir . '/build';
    }

    /**
     * Build the deployment package
     */
    public function build()
    {
        echo "🚀 Building PreProduct Plugin Deployment Package\n";
        echo "===============================================\n\n";

        // Clean previous build
        $this->clean_build_directory();

        // Create build directory
        $this->create_build_directory();

        // Copy files
        $this->copy_plugin_files();

        // Verify package
        $this->verify_package();

        // Create ZIP archive
        $this->create_zip_archive();

        echo "\n✅ Deployment package created successfully!\n";
        echo "📦 Package location: " . $this->build_dir . "/{$this->plugin_name}.zip\n";
        echo "📁 Directory location: " . $this->build_dir . "/{$this->plugin_name}/\n";
    }

    /**
     * Clean the build directory
     */
    private function clean_build_directory()
    {
        echo "🧹 Cleaning build directory...\n";
        
        if (is_dir($this->build_dir)) {
            $this->delete_directory($this->build_dir);
        }
        
        echo "   ✓ Build directory cleaned\n";
    }

    /**
     * Create build directory structure
     */
    private function create_build_directory()
    {
        echo "📁 Creating build directory structure...\n";
        
        $plugin_build_dir = $this->build_dir . '/' . $this->plugin_name;
        
        if (!mkdir($plugin_build_dir, 0755, true)) {
            throw new Exception("Failed to create build directory: $plugin_build_dir");
        }
        
        echo "   ✓ Created: $plugin_build_dir\n";
    }

    /**
     * Copy plugin files to build directory
     */
    private function copy_plugin_files()
    {
        echo "📋 Copying plugin files...\n";
        
        $source = realpath($this->source_dir);
        $destination = $this->build_dir . '/' . $this->plugin_name;
        
        $this->copy_directory($source, $destination);
        
        echo "   ✓ Plugin files copied\n";
    }

    /**
     * Copy directory with exclusions
     */
    private function copy_directory($source, $destination)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relative_path = str_replace($source . DIRECTORY_SEPARATOR, '', $item->getPathname());
            
            // Check if this item should be excluded
            if ($this->should_exclude($relative_path)) {
                continue;
            }

            $target = $destination . DIRECTORY_SEPARATOR . $relative_path;

            if ($item->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                copy($item, $target);
            }
        }
    }

    /**
     * Check if a file/directory should be excluded
     */
    private function should_exclude($path)
    {
        foreach ($this->exclude_patterns as $pattern) {
            // Check for exact matches or wildcard patterns
            if ($pattern === $path || 
                strpos($path, $pattern) === 0 || 
                fnmatch($pattern, $path) ||
                fnmatch($pattern, basename($path))) {
                return true;
            }
            
            // Check if any part of the path matches
            $path_parts = explode(DIRECTORY_SEPARATOR, $path);
            foreach ($path_parts as $part) {
                if ($part === $pattern || fnmatch($pattern, $part)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Verify the deployment package
     */
    private function verify_package()
    {
        echo "🔍 Verifying deployment package...\n";
        
        $plugin_dir = $this->build_dir . '/' . $this->plugin_name;
        
        // Check required files exist
        $required_files = [
            'woo-preproduct.php',
            'README.md',
            'uninstall.php',
            'includes/woo-preproduct-functions.php',
            'includes/class-woo-preproduct.php'
        ];
        
        foreach ($required_files as $file) {
            $file_path = $plugin_dir . '/' . $file;
            if (!file_exists($file_path)) {
                throw new Exception("Required file missing: $file");
            }
            echo "   ✓ Required file present: $file\n";
        }
        
        // Check excluded files are not present
        $excluded_checks = [
            '.git',
            'node_modules',
            'tests',
            'build-deployment.php'
        ];
        
        foreach ($excluded_checks as $excluded) {
            $excluded_path = $plugin_dir . '/' . $excluded;
            if (file_exists($excluded_path)) {
                throw new Exception("Excluded item present: $excluded");
            }
            echo "   ✓ Excluded item not present: $excluded\n";
        }
        
        // Count PHP files
        $php_files = glob($plugin_dir . '/{,*/}*.php', GLOB_BRACE);
        echo "   ✓ PHP files in package: " . count($php_files) . "\n";
        
        // Check package size
        $size = $this->get_directory_size($plugin_dir);
        echo "   ✓ Package size: " . $this->format_bytes($size) . "\n";
    }

    /**
     * Create ZIP archive
     */
    private function create_zip_archive()
    {
        echo "📦 Creating ZIP archive...\n";
        
        $zip_file = $this->build_dir . '/' . $this->plugin_name . '.zip';
        $plugin_dir = $this->build_dir . '/' . $this->plugin_name;
        
        $zip = new ZipArchive();
        if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new Exception("Cannot create ZIP file: $zip_file");
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($plugin_dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            $file_path = $file->getRealPath();
            $relative_path = $this->plugin_name . '/' . substr($file_path, strlen($plugin_dir) + 1);
            
            if ($file->isFile()) {
                $zip->addFile($file_path, $relative_path);
            }
        }
        
        $zip->close();
        
        echo "   ✓ ZIP archive created: " . basename($zip_file) . "\n";
        echo "   ✓ ZIP size: " . $this->format_bytes(filesize($zip_file)) . "\n";
    }

    /**
     * Delete directory recursively
     */
    private function delete_directory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->delete_directory($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Get directory size in bytes
     */
    private function get_directory_size($dir)
    {
        $size = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }

    /**
     * Format bytes to human readable
     */
    private function format_bytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

// Run the deployment builder
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    try {
        $builder = new DeploymentBuilder();
        $builder->build();
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}