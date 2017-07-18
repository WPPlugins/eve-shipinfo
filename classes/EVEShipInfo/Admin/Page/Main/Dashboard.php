<?php

class EVEShipInfo_Admin_Page_Main_Dashboard extends EVEShipInfo_Admin_Page_Tab
{
    const ERROR_DATABASE_VERSION_NOT_IN_DATA_FILE = 18001;
    
    const ERROR_CANNOT_UNPACK_DATA_FILE = 18002;
    
    const ERROR_DATABASE_VERSION_FILE_EMPTY = 18003;
    
    const ERROR_CANNOT_REMOVE_DATA_FILE = 18004;
    
    const ERROR_CANNOT_COPY_DATA_FILE = 18005;
    
    public function getTitle()
    {
        return sprintf(
        	__('%1$s v%2$s Dashboard', 'eve-shipinfo'),
        	EVEShipInfo::APPNAME,
        	$this->plugin->getVersion()
        );
    }
    
    protected function _render()
    {
        if(isset($_REQUEST['action'])) {
            $method = 'renderAction_'.$_REQUEST['action'];
            if(method_exists($this, $method)) {
                return $this->$method();
            }
        }
        
        $html = '';
        
        $this->checkSystem();
        
        $content = ''; 
        if(!empty($this->messages)) {
            $status = '<b style="color:#cc0000;">'.__('Warning', 'eve-shipinfo').'</b>';
            $content .=
            '<ul>';
                foreach($this->messages as $message) {
                    $content .= 
                    '<li>'.
                        $message.
                    '</li>';
                }
                $content .=
            '</ul>';
        } else {
            $status = '<b style="color:#00cc00">'.__('OK', 'eve-shipinfo').'</b>';
            $content .= __('Congratulations, everything seems to be in order.', 'eve-shipinfo');
        }
        
        $minified = '';
        if($this->plugin->isJSMinified()) {
        	$minified = '<span class="text-success">'.__('minified', 'eve-shipinfo').'</span>';
        } else {
        	$minified = '<span class="text-muted">'.__('not minified', 'eve-shipinfo').'</span>';
        }
        
        $content .= 
        '<p>'.
        	sprintf(
        		__('%1$s and %2$s files are currently %3$s (%4$schange%5$s).', 'eve-shipinfo'),
        		'JavaScript',
        		'CSS',
        		'<b>'.$minified.'</b>',
        		'<a href="'.$this->plugin->getAdminSettingsURL().'">',
        		'</a>'
        	).
        '</p>';
        
        $html .= $this->ui->createStuffBox(__('System health status:', 'eve-shipinfo').' '.$status)
        ->setContent($content)
        ->render();
        
        $html .= $this->renderDataFiles();
        
        $html .= $this->ui->createStuffBox(__('Ship screenshots bundle', 'eve-shipinfo'))
        ->setContent($this->renderScreenshotsBundle())
        ->render();
        
        return $html;
    }
    
    protected $updateForm;
    
    protected function renderAction_updateDatafile()
    {
        $form = $this->createForm(
            'dashboardUploadDatafile'
        );
         
        $form->setSubmitLabel(__('Upload and update', 'eve-shipinfo'));
        $form->setSubmitIcon($this->ui->icon()->upload());
        $form->addHiddenVar('action', 'updateDatafile');
         
        $form->addUpload('datafile', __('Data ZIP file', 'eve-shipinfo'))
        ->setRequired()
        ->setAccept('application/zip')
        ->setDescription(
            '<b>'.__('Howto:', 'eve-shipinfo').'</b> '.
            sprintf(
                __('You can find updated data files on the plugin\'s official %1$sproject page%2$s.', 'eve-shipinfo'),
                '<a href="'.$this->plugin->getHomepageDownloadURL().'" target="_blank">',
                '</a>'
            ).' '.
            __('Simply upload the updated data file here (should be a single ZIP file) to update your local ships and modules database.', 'eve-shipinfo')
        );
        
        $form->addCheckbox('replace', __('Replace existing data', 'eve-shipinfo'))
        ->setDescription(
        	__('Used mostly for development reasons.', 'eve-shipinfo').' '.
        	__('Allows re-importing a data file, but only if it has the same version as the currently installed one.', 'eve-shipinfo')
       	);
        
        $this->updateForm = $form;

        if($form->validate()) {
            return $this->updateDatafile();
        }
        
        return $this->ui->createStuffBox(__('Upload a data file', 'eve-shipinfo'))
        ->setContent($form->render())
        ->render();
    }
    
   /**
    * Called when the data file update form is valid: extracts the
    * uploaded data file to a temporary folder, checks that it's 
    * okay and inserts the updated data.
    * 
    * @return string
    */
    protected function updateDatafile()
    {
        if(!$this->updateForm->validate()) {
            return;
        }
         
        /* @var $el EVEShipInfo_Admin_UI_Form_Element_Upload */
        $el = $this->updateForm->getElementByName('datafile');
        $zipPath = $el->getPath();
        $unpackFolder = $this->plugin->getDir().'/data/temp/extract/'.md5($zipPath);
        
        $values = $this->updateForm->getValues();
        $replace = false;
        if($values['replace'] == 'yes') {
        	$replace = true;
        }
        
        WP_Filesystem();
        
        /* @var $wp_filesystem WP_Filesystem_Base */
        global $wp_filesystem;
         
        $result = unzip_file($zipPath, $unpackFolder);
        if(is_wp_error($result)) {
            /* @var $result WP_Error */
            throw new EVEShipInfo_Exception(
                'Cannot unpack uploaded file',
                sprintf(
                    'Tried unzipping the file [%s] to the folder [%s]. Native error: [#%s] [%s].',
                    $zipPath,
                    $unpackFolder,
                    $result->get_error_code(),
                    $result->get_error_message()
                ),
                self::ERROR_CANNOT_UNPACK_DATA_FILE
            );
        }

        $versionFile = $unpackFolder.'/db-version.txt';
        if(!file_exists($versionFile)) {
            throw new EVEShipInfo_Exception(
                'Missing version', 
                'The database version was not included in the uploaded data file.', 
                self::ERROR_DATABASE_VERSION_NOT_IN_DATA_FILE
            );
        }
        
        $version = file_get_contents($versionFile);
        if(empty($version)) {
            throw new EVEShipInfo_Exception(
                'No version information available', 
                'The database version file was empty.', 
                self::ERROR_DATABASE_VERSION_FILE_EMPTY
            );
        }

        $infoNew = EVEShipInfo::parseVersion($version);
        $infoOld = EVEShipInfo::parseVersion($this->plugin->getDataVersion());
        
        if($infoNew['date'] < $infoOld['date']) {
            return $this->ui->createStuffBox(__('Update the database', 'eve-shipinfo'))
            ->makeWarning()
            ->setContent(
                '<p>'.
                    __('A newer database is already installed.', 'eve-shipinfo').
                '</p>'.
                '<p>'.
                    $this->ui->button(__('OK', 'eve-shipinfo'))
                    ->link($this->getURL()).
                '</p>'
            )
            ->render();
        }
        
        if($infoNew['date'] == $infoOld['date'] && !$replace) {
        	return $this->ui->createStuffBox(__('Update the database', 'eve-shipinfo'))
        	->makeWarning()
        	->setContent(
        		'<p>'.
        			__('An equal database is already installed.', 'eve-shipinfo').
        		'</p>'.
        		'<p>'.
        		$this->ui->button(__('OK', 'eve-shipinfo'))
        		->link($this->getURL()).
        		'</p>'
        	)
        	->render();
        }
        
        $dataFilePath = $this->plugin->getDataArchivePath();
        if(file_exists($dataFilePath) && !unlink($dataFilePath)) {
            throw new EVEShipInfo_Exception(
                'Cannot remove existing data file.', 
                sprintf(
                    'Tried deleting the file [%s]',
                    $dataFilePath
                ), 
                self::ERROR_CANNOT_REMOVE_DATA_FILE
            );
        }
        
        if(!copy($zipPath, $dataFilePath)) {
            throw new EVEShipInfo_Exception(
                'Could not copy the data file.', 
                sprintf(
                    'Tried copying the file [%s] to [%s].',
                    $zipPath,
                    $dataFilePath    
                ), 
                self::ERROR_CANNOT_COPY_DATA_FILE
            );
        }
        
        // clean up uploaded file and temporary extraction folder
        $wp_filesystem->delete($zipPath);
        $wp_filesystem->rmdir($unpackFolder, true);

        return $this->renderAction_setUpDatabase($replace);
    }
    
    protected function renderAction_setUpDatabase($replace)
    {
        $this->plugin->handle_dataFileUploaded($replace);
        
        return $this->ui->createStuffBox(__('Update the database', 'eve-shipinfo'))
        ->makeSuccess()
        ->setContent(
            '<p>'.
                sprintf(
                    __('The database version %1$s has been installed.', 'eve-shipinfo'),
                    '<code>'.$this->plugin->getDataVersion().'</code>'
                ).
            '</p>'.
            '<p>'.
                $this->ui->button(__('Back', 'eve-shipinfo'))
                ->link($this->getURL()).
            '</p>'
        )
        ->render();
    }
    
    protected function renderDataFiles()
    {
        $installedVersion = $this->plugin->getDataVersion();
        if(!$this->plugin->hasDataArchive() || empty($installedVersion)) 
        {
            $html = 
            '<p class="text-error">'.
                '<b>'.__('No database installed.', 'eve-shipinfo').'</b>'.
            '</p>'.
            $this->ui->button(__('Upload data file...', 'eve-shipinfo'))
            ->link($this->getURL(array('action' => 'updateDatafile')))
            ->makePrimary()
            ->setIcon($this->ui->icon()->upload());
        } 
        else 
        {
            $this->plugin->addScript('admin/Dashboard.js', array('jquery'));
            
            $html =
            '<p>'.
            __('Version:', 'eve-shipinfo').' <code>'.$this->plugin->getDataVersion().'</code> '.
            '</p>'.
            '<p id="updatecheck-uptodate" style="display:none" class="text-success">'.
                __('Your database is up to date.', 'eve-shipinfo').
            '</p>'.
            '<p id="updatecheck-available" style="display:none" class="text-warning">'.
                sprintf(
                	__('An update is available: version %1$s', 'eve-shipinfo'),
                	'<code id="updatecheck-remoteversion"></code>'
               	).' '.
                sprintf(
                    __('Get it from the %1$splugin project page%2$s.', 'eve-shipinfo'),
                    '<a href="'.$this->plugin->getHomepageDownloadURL().'" target="_blank">',
                    '</a>'
                ).
            '</p>'.
            '<p id="updatecheck-error" style="display:none">'.
                '<b class="text-error">'.__('The online version could not be checked.', 'eve-shipinfo').'</b> '.
                sprintf(
                    __('You can visit the %1$splugin project page%2$s to check manually if you like.', 'eve-shipinfo'),
                    '<a href="'.$this->plugin->getHomepageDownloadURL().'" target="_blank">',
                    '</a>'
                ).
            '</p>'.
            $this->ui->button(__('Check for update', 'eve-shipinfo'))
            ->click('EVEShipInfo_Dashboard.CheckForUpdate()')
            ->setIcon($this->ui->icon()->update()).
            ' '.
            $this->ui->button(__('Upload data file...', 'eve-shipinfo'))
            ->link($this->getURL(array('action' => 'updateDatafile')))
            ->setIcon($this->ui->icon()->upload());
        }
        
        
        return $this->ui->createStuffBox(__('Data files', 'eve-shipinfo'))
        ->setContent($html)
        ->render();
    }
    
    protected function renderScreenshotsBundle()
    {
        $folder = $this->plugin->getGalleryPath();
        if(!is_dir($folder)) {
            return
            '<p>'.
                __('No screenshots bundle is installed.', 'eve-shipinfo').
            '</p>'.
            '<p>'.
                sprintf(
                    __('To download a screenshots bundle, go to the %1$splugin download page%2$s.', 'eve-shipinfo'),
                    '<a href="'.$this->plugin->getHomepageDownloadURL().'">',
                    '</a>'
                ).
            '</p>';
        }
        
        $versionFile = $folder.'/version.txt';
        $html = '';
        if(file_exists($versionFile)) {
            $html .=
            '<p>'.
                sprintf(
                    __('The screenshot bundle %1$s is installed.', 'eve-shipinfo'),
                    '<b>v'.file_get_contents($versionFile).'</b>'
                ).
            '</p>';
        } else {
            $html .= 
            '<p>'.
                __('An older screenshot bundle seems to be installed.', 'eve-shipinfo').
            '</p>';
        }
        
        $html .=
        '<p>'.
            sprintf(
                __('To check for updates, view the %1$splugin download page%2$s.', 'eve-shipinfo'),
                '<a href="'.$this->plugin->getHomepageDownloadURL().'">',
                '</a>'
            ).
        '</p>';
        
        return $html;
    }
    
    protected $messages = array();
    
    protected function checkSystem()
    {
    	if(!$this->plugin->hasDataArchive()) {
    		$this->messages[] = '<b>'.__('No data files found.', 'eve-shipinfo').'</b> '.
    		__('Due to WordPress\'s policies, the data files cannot be bundled with the plugin.', 'eve-shipinfo').' '.
    		__('Please use the data file upload below to install the data files. ', 'eve-shipinfo').' '.
    		__('You will find all necessary instructions there.', 'eve-shipinfo');
    		
    	} 
    	else if(!$this->plugin->isDatabaseUpToDate()) 
    	{
            $this->messages[] =
            '<b>'.__('The database needs to be updated.', 'eve-shipinfo') . '</b> ' .
            __('This is not done automatically, as it can take a little while to unpack the ships and module database.', 'eve-shipinfo') . ' ' .
            '<p>'.
            $this->ui->button(__('Update database', 'eve-shipinfo'))
            ->setIcon($this->ui->icon()->update())
            ->link($this->getURL(array('action' => 'setUpDatabase'))).
            '</p>';
        }
        
        $folder = $this->plugin->getDataFolder();
        if(!is_dir($folder) || !is_writable($folder)) {
        	$this->messages[] = 
         	'<b>'.
         		sprintf(
         			__('The plugin\'s %1$s subfolder is not writable.', 'eve-shipinfo'), 
         			'<code>'.basename($folder).'</code>'
         		).
         	'</b> '.
        	__('Please ensure that the folder is writable so the data files can be unpacked correctly.', 'eve-shipinfo');
        }
        
        if(!$this->plugin->isBlogURLRewritingEnabled()) {
            $this->messages[] = __('Permalinks are not enabled, virtual pages will not work even if you have enabled them.', 'eve-shipinfo');
        }
        
        if(!$this->plugin->getDummyPage()) {
            $this->messages[] = 
                __('Could not find any pages.', 'eve-shipinfo').' '.
                __('For virtual pages to work, you have to create at least one page.', 'eve-shipinfo').' '.
                __('It does not need to have any content, just create an empty page.', 'eve-shipinfo');
        }
    }
}