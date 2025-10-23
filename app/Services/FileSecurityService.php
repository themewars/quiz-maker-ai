<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class FileSecurityService
{
    /**
     * Validate file content to prevent malicious uploads
     */
    public static function validateFileContent(UploadedFile $file): bool
    {
        try {
            // Get file content
            $content = file_get_contents($file->getPathname());
            
            // Check for PHP tags
            if (self::containsPhpTags($content)) {
                Log::warning('File upload blocked: Contains PHP tags', [
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType()
                ]);
                return false;
            }
            
            // Check for executable signatures
            if (self::containsExecutableSignatures($content)) {
                Log::warning('File upload blocked: Contains executable signatures', [
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType()
                ]);
                return false;
            }
            
            // Check for suspicious patterns
            if (self::containsSuspiciousPatterns($content)) {
                Log::warning('File upload blocked: Contains suspicious patterns', [
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType()
                ]);
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('File content validation error', [
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Check for PHP tags in file content
     */
    private static function containsPhpTags(string $content): bool
    {
        $phpPatterns = [
            '/<\?php/i',
            '/<\?=/i',
            '/<\?/i',
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload=/i',
            '/onerror=/i',
            '/onclick=/i'
        ];
        
        foreach ($phpPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for executable file signatures
     */
    private static function containsExecutableSignatures(string $content): bool
    {
        $executableSignatures = [
            "\x4D\x5A", // PE/COFF executable
            "\x7F\x45\x4C\x46", // ELF executable
            "\xFE\xED\xFA", // Mach-O executable
            "\xCE\xFA\xED\xFE", // Mach-O executable (reverse)
            "\xCA\xFE\xBA\xBE", // Java class file
            "\x50\x4B\x03\x04", // ZIP/JAR file
        ];
        
        foreach ($executableSignatures as $signature) {
            if (strpos($content, $signature) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for suspicious patterns
     */
    private static function containsSuspiciousPatterns(string $content): bool
    {
        $suspiciousPatterns = [
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/shell_exec\s*\(/i',
            '/passthru\s*\(/i',
            '/proc_open\s*\(/i',
            '/popen\s*\(/i',
            '/file_get_contents\s*\(/i',
            '/fopen\s*\(/i',
            '/fwrite\s*\(/i',
            '/base64_decode\s*\(/i',
            '/gzinflate\s*\(/i',
            '/str_rot13\s*\(/i',
            '/create_function\s*\(/i',
            '/assert\s*\(/i',
            '/preg_replace\s*\(/i',
            '/call_user_func\s*\(/i',
            '/call_user_func_array\s*\(/i',
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Validate image file content
     */
    public static function validateImageContent(UploadedFile $file): bool
    {
        try {
            // Check if it's actually an image
            $imageInfo = getimagesize($file->getPathname());
            if ($imageInfo === false) {
                return false;
            }
            
            // Check image dimensions
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            
            if ($width < 100 || $height < 100) {
                return false;
            }
            
            if ($width > 2000 || $height > 2000) {
                return false;
            }
            
            // Check for valid image types
            $validTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
            if (!in_array($imageInfo[2], $validTypes)) {
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Image validation error', [
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
