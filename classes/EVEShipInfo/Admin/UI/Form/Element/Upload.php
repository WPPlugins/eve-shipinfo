<?php

class EVEShipInfo_Admin_UI_Form_Element_Upload extends EVEShipInfo_Admin_UI_Form_ElementInput
{
	public function getType()
	{
		return 'file';
	}
	
   /**
    * Sets the mime type this element accepts, e.g. "text/html".
    * 
    * @param string $mime
    * @return EVEShipInfo_Admin_UI_Form_Element
    */
	public function setAccept($mime)
	{
		return $this->setAttribute('accept', $mime);
	}
	
	public function validate()
	{
		if($this->validated) {
			return $this->valid;
		}

		$this->validated = true;
		$this->valid = false;
		
		$this->clearSetting('name');
		$this->clearSetting('content');
		
		if(!isset($_FILES[$this->name])) {
			$this->validationMessage = __('No uploaded file found.', 'eve-shipinfo');
			return false;
		}
		
		switch($_FILES[$this->name]['error']) {
			case UPLOAD_ERR_FORM_SIZE:
			case UPLOAD_ERR_INI_SIZE:
				$this->validationMessage = __('The uploaded file is too big.', 'eve-shipinfo');
				return false;
				
			case UPLOAD_ERR_PARTIAL:
				$this->validationMessage = __('The file was only partially uploaded.', 'eve-shipinfo');
				return false; 

			case UPLOAD_ERR_NO_FILE:
				$this->validationMessage = __('No file uploaded.', 'eve-shipinfo');
				return false;

			case UPLOAD_ERR_EXTENSION:
			case UPLOAD_ERR_CANT_WRITE:
			case UPLOAD_ERR_NO_TMP_DIR:
				$this->validationMessage = __('Could not write uploaded file to disk, server configuration error.', 'eve-shipinfo');
				return false;
		}
		
		$tmpPath = $this->plugin->getDir().'/data/uploads/'.md5(microtime(true));
		$tmpDir = dirname($tmpPath);
		if(!is_dir($tmpDir) && !mkdir($tmpDir, 0777, true)) {
		    $this->validationMessage = sprintf(
		        __('Could not create the temporary upload folder, please check that the path [%1$s] is writable.', 'eve-shipinfo'),
		        $tmpDir
	        );
		    return false;
		}
		
		if(!move_uploaded_file($_FILES[$this->name]['tmp_name'], $tmpPath)) {
		    $this->validationMessage = __('The uploaded file could not be moved, please try again.', 'eve-shipinfo');
		    return false;
		}
		
		$this->valid = true;
		
		$this->setSetting('path', $tmpPath);
		$this->setSetting('name', $_FILES[$this->name]['name']);
		
		return true;
	}
	
	public function getValue()
	{
		if(!$this->form->isSubmitted()) {
			return '';
		}
		
		return $this->getSetting('name');
	}
	
	public function getContent()
	{
		if(!$this->form->isSubmitted()) {
			return '';
		}
		
		$content = file_get_contents($this->getSetting('path'));
		if($content) {
		    return $content;
		}
		
		return '';
	}
	
	public function getPath()
	{
	    if(!$this->form->isSubmitted()) {
	        return '';
	    }
	    
	    return $this->getSetting('path');
	}
}