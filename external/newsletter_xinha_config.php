<?php
header('content-type: text/javascript');
?>

xinha_editors = null;
    xinha_init    = null;
    xinha_config  = null;
    xinha_plugins = null;

    // This contains the names of textareas we will make into Xinha editors
      xinha_plugins = xinha_plugins ? xinha_plugins :
      ['ContextMenu', 'Stylist', 'FindReplace', 'PasteText', 'ExtendedFileManager', 'TableOperations', 'InsertAnchor', 'HtmlEntities'];
     xinha_editors = xinha_editors ? xinha_editors :
      ['bodyhtml'];
    xinha_init = xinha_init ? xinha_init : function()
    {
         // THIS BIT OF JAVASCRIPT LOADS THE PLUGINS, NO TOUCHING  :)
         if(!Xinha.loadPlugins(xinha_plugins, xinha_init)) return;

       xinha_config = xinha_config ? xinha_config : new Xinha.Config();
        <?php Jojo::runHook('xinha_config_start'); ?>
        xinha_config.toolbar =
         [
           ["popupeditor"],
           ["separator","formatblock","bold","italic","underline","strikethrough"],
           ["separator","subscript","superscript"],
           ["separator","justifyleft","justifycenter","justifyright","justifyfull"],
           ["separator","insertorderedlist","insertunorderedlist","outdent","indent"],
           ["separator","inserthorizontalrule","createlink","insertimage","inserttable"],
           ["separator","undo","redo"],
           ["separator","killword","clearfonts","removeformat","toggleborders","splitblock"],
           ["separator","htmlmode","showhelp","about"]
         ];

        //xinha_config.stylistLoadStylesheet("<?php echo _SITEURL; ?>/css/styles.css");
        xinha_config.pageStyleSheets = ["<?php echo _SITEURL; ?>/css/xinha.css", "<?php echo _SITEURL; ?>/css/newsletter.css"];
        xinha_config.baseHref = "<?php echo _SITEURL; ?>/";
        xinha_config.sevenBitClean = false;

	
        xinha_config.stripBaseHref = false;	

        if (xinha_config.ExtendedFileManager) {
                with (xinha_config.ExtendedFileManager)
                {
                    <?php
	
                    // define backend configuration for the plugin
                    $IMConfig = array();
                    $IMConfig['images_dir'] = _DOWNLOADDIR . '/images/';
                    $IMConfig['images_url'] = _SITEURL . '/downloads/images/';
                    $IMConfig['files_dir'] = _DOWNLOADDIR . '/files/';
                    $IMConfig['files_url'] = _SITEURL . '/downloads/files/';
                    $IMConfig['thumbnail_prefix'] = 't_';
                    $IMConfig['thumbnail_dir'] = 't';
                    $IMConfig['resized_prefix'] = 'resized_';
                    $IMConfig['resized_dir'] = '';
                    $IMConfig['tmp_prefix'] = '_tmp';
                    $IMConfig['view_type'] =  Jojo::getOption('xinha_viewtype','thumbview');
                    $IMConfig['allow_upload'] = true;
                    $IMConfig['max_filesize_kb_image'] = Jojo::getOption('max_imageupload_size','2000');           
                    $IMConfig['max_filesize_kb_link'] = Jojo::getOption('max_fileupload_size','5000');
            
                    // Maximum upload folder size in Megabytes.
                    // Use 0 to disable limit
                    $IMConfig['max_foldersize_mb'] = 0;
            
                    $IMConfig['allowed_image_extensions'] = array("jpg","gif","png");
                    $IMConfig['allowed_link_extensions'] = array("jpg","gif","pdf","ip","txt","doc",
                                                                 "psd","png","html","swf",
                                                                 "xml","xls");
            
                    require_once _BASEPLUGINDIR . '/jojo_core/external/xinha/contrib/php-xinha.php';
                    xinha_pass_to_php_backend($IMConfig);
                    ?>
                }
        }
	
  xinha_editors = Xinha.makeEditors(xinha_editors, xinha_config, xinha_plugins);

  xinha_editors.bodyhtml.config.width = '780px';
  xinha_editors.bodyhtml.config.height = '460px';

  Xinha.startEditors(xinha_editors);
  window.onload = null;
}
window.onload = xinha_init;