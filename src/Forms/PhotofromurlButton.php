<?php

namespace Pixelspin\Photofromurl\Forms;

use SilverStripe\Assets\Filesystem;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Director;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\Requirements;

class PhotofromurlButton extends FormField {

    private static $allowed_actions = [
        'handle'
    ];

    private $object;
    private $relationname;
    private $targetFolder;

    public function __construct($action, $title = 'Add photo from url', $object, $relationname, $targetFolder = 'DownloadedPhotos')
    {
        $this->object = $object;
        $this->relationname = $relationname;
        $this->targetFolder = $targetFolder;
        Requirements::javascript('pixelspin/silverstripe-photofromurl:resources/js/photofromurl.js');
        parent::__construct($action, $title);
    }

    public function handle(){
        //Vars
        $r = $this->getRequest();
        $objectID = $r->getVar('objectid');
        $objectClass = $r->getVar('objectclass');
        $relationName = $r->getVar('relationname');
        $object = DataObject::get($objectClass)->byID($objectID);
        $relationType = $object->getRelationType($relationName);
        $url = $r->getVar('imgurl');
        $targetFolder = $r->getVar('targetfolder');

        //Handle
        $file = @file_get_contents($url);
        if($file === FALSE){
            return json_encode([
                'success' => false,
                'message' => 'Cant read the url'
            ]);
        }
        $imageSize = getimagesize($url);
        if($imageSize === false){
            return json_encode([
                'success' => false,
                'message' => 'Invalid photo'
            ]);
        }
        $mime = $imageSize['mime'];
        $ext = false;
        switch ($mime) {
            case "image/gif":
                $ext = '.gif';
                break;
            case "image/jpg":
            case "image/jpeg":
                $ext = '.jpg';
                break;
            case "image/png":
                $ext = '.png';
                break;
        }
        if($ext === false){
            return json_encode([
                'success' => false,
                'message' => 'Invalid photo'
            ]);
        }

        //Save photo
        $assetsPath = Director::baseFolder() . '/assets/';
        $saveFolder = Folder::find_or_make($targetFolder ? $targetFolder : 'DownloadedPhotos');
        $folderPath = $assetsPath . $saveFolder->getFilename();
        Filesystem::makeFolder($folderPath);
        $imagePath = $folderPath . uniqid() . $ext;
        file_put_contents($imagePath, $file);
        $image = new Image();
        $image->setFromLocalFile($imagePath);
        $image->ParentID = $saveFolder->ID;
        $image->write();

        if($relationType == 'many_many'){
            $object->$relationName()->add($image);
        }else if($relationType == 'has_one'){
            $relationName = $relationName . 'ID';
            $object->$relationName = $image->ID;
            $object->write();
        }

        //Response
        return json_encode([
            'success' => true
        ]);
    }

    public function HandleLink(){
        return $this->Link('handle') . '?objectid=' . $this->object->ID . '&objectclass=' . get_class($this->object) . '&targetfolder=' . $this->targetFolder . '&relationname=' . $this->relationname . '&imgurl=';
    }

    public function FieldHolder($properties = array())
    {
        return $this->renderWith(self::class);
    }

}
