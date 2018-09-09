<?php

namespace App\Traits;

use Auth;
use File;
use Illuminate\Http\UploadedFile;
use Image;

trait FileUploadTrait
{
    /**
     * Delete file
     *
     * @param $path
     * @param $file_name
     */
    public function deleteFiles($path, $file_name)
    {
        $path = public_path($path.'/');

        if ($file_name !== null) {
            $file_name = json_decode($file_name);

            foreach ($file_name as $file) {
                if (File::exists($path.$file)) {
                    File::delete($path.$file);
                }
            }
        }
    }

    /**
     * Upload file
     *
     * @param $path
     * @param $new_file_name
     * @param \Illuminate\Http\UploadedFile $file
     * @param bool $use_original_name
     * @return array
     */
    public function saveFiles($path, $new_file_name, UploadedFile $file, $use_original_name = false)
    {
        $full_uploaddir_path = public_path($path.'/');

        $file_extension = mb_strtolower($file->getClientOriginalExtension());
        if ($use_original_name) {
            $file_name_without_extension = substr($file->getClientOriginalName(), 0, mb_strrpos($file->getClientOriginalName(), '.'));
            $new_file_name = $new_file_name.'-'.str_slug($file_name_without_extension);
        }
        $filename = str_slug($new_file_name).'-'.Auth::id().'-'.date('Y').'-'.date('m').'-'.date('d').'-'.mt_rand();

        // Make folder
        if (! File::isDirectory($full_uploaddir_path)) {
            File::makeDirectory($full_uploaddir_path, 0777, true, true);
        }
        
        // Make image
        $newFileName = $filename.'.'.$file_extension;
        $largeFileName = $filename.'_large'.'.'.$file_extension;
        $mediumFileName = $filename.'_medium'.'.'.$file_extension;
        $smallFileName = $filename.'_small'.'.'.$file_extension;
        $thumbFileName = $filename.'_thumb'.'.'.$file_extension;

        $largeSize = 1200;
        $mediumSize = 600;
        $smallSize = 300;
        $thumbSize = 50;

        // Save large
        Image::make($file)->resize($largeSize, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save($full_uploaddir_path.$largeFileName);

        // Save medium
        Image::make($file)->resize($mediumSize, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save($full_uploaddir_path.$mediumFileName);

        // Save small
        Image::make($file)->resize($smallSize, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save($full_uploaddir_path.$smallFileName);

        // Save thumb
        Image::make($file)->resize($thumbSize, $thumbSize, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save($full_uploaddir_path.$thumbFileName);

        // Save original
        Image::make($file)->save($full_uploaddir_path.$newFileName);

        return [
            'original' => $newFileName,
            'large' => $largeFileName,
            'medium' => $mediumFileName,
            'small' => $smallFileName,
            'thumb' => $thumbFileName,
        ];
    }
}