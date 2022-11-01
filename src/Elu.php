<?php

namespace Laravel\Elu;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Image;

class Elu
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
	
	public function __construct()
	{
		$this->width = config("elu.width");
		$this->height = config("elu.height");
		$this->valid_mimes = config("elu.mime_type");
		$this->max_file_upload_size = config("elu.max_size");
		$this->max_no_of_file_to_upload = config("elu.max_to_upload");

		$this->sub_folder = config("elu.sub_folder");

		$this->docParentFolder = config('elu.directory') ?? 'public';
	}
	
	
    public function unique_name($lenght = 13) 
	{
		$prefix = Str::lower(config("app.name"))."_".date("Ymd");
		// uniqid gives 13 chars, but you could adjust it to your needs.
        if (function_exists("random_bytes"))
		{
			$bytes = random_bytes(ceil($lenght / 2));
        }   
		elseif(function_exists("openssl_random_pseudo_bytes")) 
		{
			$bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        } 
		else 
		{
			throw new \Exception("no cryptographically secure random function available");
        }
        return substr($prefix.bin2hex($bytes), 0, $lenght);
    }

    //Returns an array/string with the renamed file as value.
    function upload($file_upload_name="image", $ignore=false)
    {
		if(!request()->hasFile($file_upload_name))
		{
			if($ignore == true)
			{
				return;
			}
			else
			{
				$this->returnError("Please upload a valid file.");
			}
		}
		
	    $file = request()->file($file_upload_name);
		
		if(is_array($file))
		{
			//We expect a maximum number of $max_no_of_file_to_upload images to upload, if it's more than then we issue an error warning.
            if(count($file) > $this->max_no_of_file_to_upload)
            {
				$this->returnError("Exceeded maximum number of files/images to upload. You can only upload ".$this->max_no_of_file_to_upload." maximum number of files/images.");
            }   
		
			//This will house the new name of the images and will be sent back to the Controller.
	        $photos = [];
		
		    //Users are allowed to upload a certain number of files so that the $_FILES array is filled accordingly. But User can skip the first box for where to place the file in the html form and go for the second, third, etc. In this case the all the arrays will be set and with the same exact count but array["Name"][0] will be empty alongside other than keys pertaining the 0-th file and this will cause a bug in the script so we wanna carter for it first.
		    for($i=0;$i<count($file);$i++)
            {
				if(!empty($file[$i]->getClientOriginalName()))
				{
					if($file[$i]->getMimeType() == null)
		            {
						continue;
		            }
	
	                $file_type = $file[$i]->getMimeType();
			
			        //if file type is not any of these specified types
			        if(!in_array($file_type, $this->valid_mimes))
					{
						$this->returnError("Invalid image/file format detected for ".$file[$i]->getClientOriginalName().". Accepted format(s) is/are:".$this->getAllowedTypes());
			        }
		    
		            if($file[$i]->getSize() > $this->max_file_upload_size)//if file is larger than a specified size.
					{
						$this->returnError($file[$i]->getClientOriginalName()." is too large. Accepted max file size is ".$this->calculateFileSize()."MB");
	                }
	
	                $stock = $file[$i]->getClientOriginalName();// takes name of file 'AS IS' from user's computer in this variable.
	                $separate = explode(".", $stock);//separates file name('image') from base-name(e.g '.jpg').
				
				    if(empty($this->name_of_file))
					{
						$uniq_name = $this->unique_name(35);//a unique name for files to be stored on server to avoid file overwriting.
                        $separate[0] = $uniq_name;// rename file from user's computer to be stored on server using the generated unique id.
				    }
				    else
					{
						//The name is mainly set when it involves upload of document for account verification. The process below helps to mitigate overriding.
					    $index = $i+1;
					    $separate[0] = $this->name_of_file.$index;
				    }
				
	                $new_name = $separate[0].".".$separate[1];//joins new file name now with base-name/extension of the image.
                  
					$img = Image::make($file[$i])->resize($this->width, $this->height, function ($constraint) {
                                                $constraint->aspectRatio();
                              })->encode($separate[1]);
						
					if(!empty($this->sub_folder))
					{
						$path = Storage::put($this->docParentFolder.'/'.$this->sub_folder.'/'.$new_name, $img->__toString());
					}
					else
					{
						$path = Storage::put($this->docParentFolder.'/'.$new_name, $img->__toString());
					}
					
                    if($path) 
					{
						$photos []= $new_name;
					}
					else
					{
						$this->returnError("Couldn't upload this file");
					}
				}
            }
		    return $photos;
	    }
		
		//This should accomodate for single Upload(s) that don't need to be joined together or kept in an array
		else
		{
			if(!empty($file->getClientOriginalName()))
			{
	            $file_type = $file->getMimeType();
			
			    //if file type is not any of these specified types
			    if(!in_array($file_type, $this->valid_mimes))
				{
					$this->returnError("Invalid image/file format detected for ".$file->getClientOriginalName().". Accepted format(s) is/are:".$this->getAllowedTypes());
					//$this->returnError("Couldn't upload this file");
			    }
		    
		        if($file->getSize() > $this->max_file_upload_size)//if file is larger than a specified size.
		        {
					$this->returnError($file->getClientOriginalName()." is too large. Accepted max file size is ".$this->calculateFileSize()."MB");
	            }
	
	            $stock = $file->getClientOriginalName();// takes name of file 'AS IS' from user's computer in this variable.
	            
				$separate = explode(".", $stock);//separates file name('image') from base-name(e.g '.jpg').
				
				if(empty($this->name_of_file))
				{
					$uniq_name = $this->unique_name(35);//a unique name for files to be stored on server to avoid file overwriting.
                    $separate[0] = $uniq_name;// rename file from user's computer to be stored on server using the generated unique id.
				}
				else
				{
					$separate[0] = $this->name_of_file;
				}
				
	            $new_name = $separate[0].".".$separate[1];//joins new file name now with base-name/extension of the image.

                $img = Image::make($file)->resize($this->width, $this->height, function ($constraint) {
                                                $constraint->aspectRatio();
												$constraint->upsize();
                              })->encode($separate[1]);
							  
				if(!empty($this->sub_folder))
				{
					Storage::put($this->docParentFolder.'/'.$this->sub_folder.'/'.$new_name, $img->__toString());
				}
				else
				{
					Storage::put($this->docParentFolder.'/'.$new_name, $img->__toString());
				}

				return $new_name;
		    }
		}	
    }

    public function remove($file)
	{
		if(is_array($file))
		{
			for($i=0;$i<count($file);$i++)
			{
				$status = Storage::delete($this->docParentFolder.'/'.$this->sub_folder.'/'.$file[$i]);
	        }
        }
        else
		{
			$status = Storage::delete($this->docParentFolder.'/'.$this->sub_folder.'/'.$file);
		}
		
		return $status;
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

	public function calculateFileSize()
	{
		return number_format($this->max_file_upload_size/1000000,2);
	}

	public function getAllowedTypes()
	{
		$mimes = "";
		
		foreach($this->valid_mimes as $mime)
		{
			$mimes []= explode('/', $mime)[1]. " ";
		}

		return trim($mimes);
	}
}
?>