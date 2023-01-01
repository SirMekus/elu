<?php

namespace Laravel\Elu;

use Illuminate\Support\Facades\Storage;
use Image;
use Laravel\Elu\EluTrait;

class Elu
{
	use EluTrait;

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

    /**
     * @param string $file_upload_name => Name (parameter) of the incoming file from the client. Default is 'image'
	 * 
	 * @param bool $ignore => In certain cases image upload may be optional, thus the application should still continue with the request processing
     *
     * @return string|array|null
    */
    function upload($file_upload_name="image", $ignore=false)
    {
		if(!request()->hasFile($file_upload_name))
		{
			if($ignore == true)
			{
				return null;
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
				$this->returnError("Exceeded maximum number of file(s)/image(s) to upload. You can only upload ".$this->max_no_of_file_to_upload." maximum number of file(s)/image(s).");
            }   
		
		    for($i=0;$i<count($file);$i++)
            {
				$typeOfFile = $this->isImage($file[$i]) ? 'image' : 'file';

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
						$this->returnError("Invalid $typeOfFile format detected for ".$file[$i]->getClientOriginalName().". Accepted format(s) is/are:".$this->getAllowedTypes());
			        }
		    
		            if($file[$i]->getSize() > $this->max_file_upload_size)//if file is larger than a specified size.
					{
						$this->returnError($file[$i]->getClientOriginalName()." is too large. Accepted max file size is ".$this->calculateFileSize($file[$i]->getSize()));
	                }
				}
            }
	    }
		
		//This should accomodate for single Upload(s) that don't need to be joined together or kept in an array
		else
		{
			if(!empty($file->getClientOriginalName()))
			{
				$typeOfFile = $this->isImage($file) ? 'image' : 'file';

	            $file_type = $file->getMimeType();
			
			    //if file type is not any of these specified types
			    if(!in_array($file_type, $this->valid_mimes))
				{
					$this->returnError("Invalid $typeOfFile format detected for ".$file->getClientOriginalName().". Accepted format(s) is/are:".$this->getAllowedTypes());
			    }
		    
		        if($file->getSize() > $this->max_file_upload_size)
		        {
					$this->returnError($file->getClientOriginalName()." is too large. Accepted max file size is ".$this->calculateFileSize($file->getSize()));
	            }
		    }
		}

		return $this->save($file);
    }


	public function save($file)
	{
		if(is_array($file))
		{
			//This will house the new name of the files and will be sent back to the Controller.
	        $photos = [];

			for($i=0;$i<count($file);$i++)
            {
				$new_name = (empty($this->name_of_file) ? $this->unique_name(35) : $this->name_of_file).".".$file[$i]->getClientOriginalExtension();

				if($this->isImage($file[$i]))
				{
					$img = Image::make($file[$i])->resize($this->width, $this->height, function ($constraint) {
                                                $constraint->aspectRatio();
                              })->encode($file[$i]->getClientOriginalExtension());

					$path = Storage::put($this->getDirectory().'/'.$new_name, $img->__toString());
				}
				else
				{
					$path = Storage::putFileAs($this->getDirectory().'/', $file[$i], $new_name);
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

			return $photos;
		}
		else
		{
			$new_name = (empty($this->name_of_file) ? $this->unique_name(35) : $this->name_of_file).".".$file->getClientOriginalExtension();

			if($this->isImage($file))
			{
				$img = Image::make($file)->resize($this->width, $this->height, function ($constraint) {
                                                $constraint->aspectRatio();
                              })->encode($file->getClientOriginalExtension());

				Storage::put($this->getDirectory().'/'.$new_name, $img->__toString());
			}
			else
			{
				Storage::putFileAs($this->getDirectory().'/', $file, $new_name);
			}

			return $new_name;
		}
	}

	/**
     * @param string|array $file => name of file to delete
     *
    */
    public function remove($file)
	{
		if(is_array($file))
		{
			for($i=0;$i<count($file);$i++)
			{
				$status = Storage::delete($this->getDirectory().'/'.$file[$i]);
	        }
        }
        else
		{
			$status = Storage::delete($this->getDirectory().'/'.$file);
		}
		
		return $status;
    }
}
?>