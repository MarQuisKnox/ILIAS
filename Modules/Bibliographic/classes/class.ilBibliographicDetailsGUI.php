<?php

require_once "./Modules/Bibliographic/classes/class.ilBibliographicEntry.php";
require_once "./Modules/Bibliographic/classes/class.ilBibliographicSetting.php";

/**
 * Class ilBibliographicDetailsGUI
 * The detailled view on each entry
 *
 * @ilCtrl_Calls ilObjBibliographicDetailsGUI: ilBibliographicGUI
 */
class ilBibliographicDetailsGUI
{

    /**
     * @var ilObjBibliographicObject
     */
    var $bibl_obj;

    /**
     * @var ilObjBibliographicEntry
     */
    var $entry;


    /**
     * @param ilObjBibliographic $bibl_obj
     * @return void
     *
     */
    public function showDetails(ilObjBibliographic $bibl_obj)
    {
        global $tpl, $ilTabs, $ilCtrl, $lng, $ilDB;
        $this->bibl_obj = $bibl_obj;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();


        $ilTabs->clearTargets();
        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, 'showContent'));

        $form->setTitle($lng->txt('detail_view'));

        // add link button if a link is defined in the settings
        $set = new ilSetting("bibl");
        $link = $set->get(strtolower($this->bibl_obj->getFiletype()));
        if(!empty($link)){
             $form->addCommandButton('autoLink', 'Link');
        }

        $this->entry = new ilBibliographicEntry($this->bibl_obj->getFiletype(), $_GET['entryId']);
        $attributes = $this->entry->getAttributes();

        //translate array key in order to sort by those keys
        foreach($attributes as $key => $attribute)
        {
            //Check if there is a specific language entry
            if($lng->exists($key))
            {
                $strDescTranslated = $lng->txt($key);
            }
            //If not: get the default language entry
            else
            {
                $arrKey = explode("_",$key);
                $strDescTranslated = $lng->txt($arrKey[0]."_default_".$arrKey[2]);
            }
            unset($attributes[$key]);
            $attributes[$strDescTranslated] = $attribute;
        }

        // sort attributes alphabetically by their array-key
        ksort($attributes, SORT_STRING);

        // render attributes to html
        foreach($attributes as $key => $attribute)
        {
            $ci = new ilCustomInputGUI($key);
            $ci->setHtml($attribute);
            $form->addItem($ci);
        }

        // generate/render links to libraries
        $settings = ilBibliographicSetting::getAll();
        foreach($settings as $set){
            if($set->getImageUrl() == ''){
                // default image
                $set->setImageUrl(ilUtil::getImagePath('lib_link_def.gif'));
            }
            $ci = new ilCustomInputGUI($set->getName());
            $ci->setHtml('<a target="_blank" href="'.$set->generateLibraryLink($this->entry, $this->bibl_obj->getFiletype()).'"><img src="'.$set->getImageUrl().'"></a>');
            $form->addItem($ci);
        }

        // set content and title
        $tpl->setContent($form->getHTML());

        //Permanent Link
        $tpl->setPermanentLink("bibl", $bibl_obj->getRefId(),"_".$_GET['entryId']);

        /*$entry_attributes = $this->entry->getAttributes();
        foreach($entry_attributes as $key => $value){
            echo "<script type='text/javascript'>alert($key);</script>";
        }*/


    }

    /**
     * generate URL to library
     *
    public function generateLibraryLink($base_url){
        global $ilDB, $ilCtrl;

        // get the link/logic from Settings
        $bibl_settings = new ilSetting("bibl");

        // get entry's and settings' attributes
        $entry_attributes = $this->entry->getAttributes();
        $attr_order = explode(",", $bibl_settings->get(strtolower($this->bibl_obj->getFiletype())."_ord"));

        if($attr_order[0] == "" && sizeof($attr_order) == 1){
            // set default
            switch($this->bibl_obj->getFiletype()){
                case 'bib':
                    $attr_order = array("isbn", "issn", "title");
                    break;
                case 'ris':
                    $attr_order = array("sn","ti");
                    break;
                default:
                    $attr_order = array("isbn");
            }

        }

        switch($this->bibl_obj->getFiletype()){
            case 'bib':
                $prefix = "bib_default_";
                break;
            case 'ris':
                $prefix = "ris_default_";
                break;
        }

        // get first existing attribute (in order of the settings or default if nothing set)
        $i = 0;
        while(empty($entry_attributes[$prefix.trim(strtolower($attr_order[$i]))]) && ($i < 10)){
            $i++;
        }
        $attr = trim(strtolower($attr_order[$i]));
        $value = $entry_attributes[$prefix.$attr];

        switch($attr){
            case 'ti':
                $attr="title";
                break;
            case 'sn':
                if(strlen($value)<=9){
                    $attr="issn";
                }else{
                    $attr="isbn";
                }
                break;
            case 'pb':
                $attr="publisher";
                break;
            default:
        }


        // generate and return full link
        $full_link = $base_url."?".$attr."=".$value;
        return $full_link;

    }*/



}

?>