<?php

namespace Laravel\Elu;
use Illuminate\Support\Str;

trait EluTrait
{
    //This is the folder from the "public" directory that should be accessible from the web 
	public $docParentFolder;
	public $sub_folder;

	public $width;
	public $height;
	public $valid_mimes;
	public $max_file_upload_size;
	public $max_no_of_file_to_upload;
	public $name_of_file;

	public function unique_name($length = 13) 
	{
		$prefix = Str::lower(config("app.name"))."_".date("Ymd");
		// uniqid gives 13 chars, but you could adjust it to your needs.
        if (function_exists("random_bytes"))
		{
			$bytes = random_bytes(ceil($length / 2));
        }   
		elseif(function_exists("openssl_random_pseudo_bytes")) 
		{
			$bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } 
		else 
		{
			throw new \Exception("no cryptographically secure random function available");
        }
        return substr($prefix.bin2hex($bytes), 0, $length);
    }

	public function getDirectory()
	{
		return !empty($this->sub_folder) ? $this->docParentFolder.'/'.$this->sub_folder : $this->docParentFolder;
	}

	public function isImage($file)
	{
		return (explode('/', $file->getMimeType())[0] == 'image') ? true : false;	
	}

    public function returnError($msg)
	{
		if(request()->ajax())
        {
			abort(422, $msg);
        }
        {
            return back()->with('elu', $msg);
        }
	}

	public function calculateFileSize($bytes)
	{
		$bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

		$result= "";

        foreach($arBytes as $arItem)
        {
			if($bytes >= $arItem["VALUE"])
            {
				$result = $bytes / $arItem["VALUE"];
			    $result = strval(round($result, 2))." ".$arItem["UNIT"];
                break;
            }
        }
        return $result;
	}

	public function getAllowedTypes()
	{
		$mimes = "";
		
		foreach($this->valid_mimes as $mime)
		{
			$mimes .= explode('/', $mime)[1]. ", ";
		}

		return trim($mimes, ", ''");
	}
}