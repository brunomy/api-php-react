<?php
require_once '../Core/Loader.php';

use System\Core\System;
$sistema = new System();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Supr admin</title>
    <base href="<?php echo $sistema->system_path; ?>" />
    <!-- Mobile Specific Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Force IE9 to render in normla mode -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- Le styles -->

    <!-- Use new way for google web fonts 
    http://www.smashingmagazine.com/2012/07/11/avoiding-faux-weights-styles-google-web-fonts -->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css' /> <!-- Headings -->
    <link href='https://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css' /> <!-- Text -->
    <!--[if lt IE 9]>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:700" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Droid+Sans:400" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Droid+Sans:700" rel="stylesheet" type="text/css" />
    <![endif]-->

    <!-- Core stylesheets do not remove -->
    <link href="css/bootstrap.css" rel="stylesheet" />
    <!-- Plugins stylesheets (all plugin custom css) -->
    <link href="css/plugins.css" rel="stylesheet" />
    <link href="css/supr-theme/jquery.ui.supr.css" rel="stylesheet" type="text/css"/>
    <link href="css/icons.css" rel="stylesheet" type="text/css" />

    
    <link href="plugins/files/elfinder/elfinder.css" type="text/css" rel="stylesheet" />
    <link href="plugins/files/plupload/jquery.ui.plupload/css/jquery.ui.plupload.css" type="text/css" rel="stylesheet" />
    
    <!-- Main stylesheets -->
    <link href="css/main.css" rel="stylesheet" type="text/css" /> 

    <!-- Custom stylesheets ( Put your own changes here ) -->
    <link href="css/custom.css" rel="stylesheet" type="text/css" />  

    <!--[if IE 8]><link href="css/ie8.css" rel="stylesheet" type="text/css" /><![endif]-->

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script type="text/javascript" src="js/libs/excanvas.min.js"></script>
      <script type="text/javascript" src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
      <script type="text/javascript" src="js/libs/respond.min.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="images/favicon.ico" />
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="images/apple-touch-icon-144-precomposed.png" />
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="images/apple-touch-icon-114-precomposed.png" />
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="images/apple-touch-icon-72-precomposed.png" />
    <link rel="apple-touch-icon-precomposed" href="images/apple-touch-icon-57-precomposed.png" />
    
    <!-- Windows8 touch icon ( http://www.buildmypinnedsite.com/ )-->
    <meta name="application-name" content="Supr"/> 
    <meta name="msapplication-TileColor" content="#3399cc"/> 

    <!-- Load modernizr first -->
    <script type="text/javascript" src="js/libs/modernizr.js"></script>
    
    </head>
      
    <body>
      
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default toggle">
                    <div class="panel-body noPad">
                        <div id="elfinder"></div>
                        <div id="html4_uploader">O seu navegador não tem suporte HTML 4.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Le javascript
        ================================================== -->
        <!-- Important plugins put in all pages -->
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
        <script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
        <!-- TinyMCE Popup class (REQUIRED) -->
        <script type="text/javascript" src="plugins/files/elfinder/elfinder.min.js"></script>
        <script type="text/javascript" src="plugins/files/plupload/plupload.js"></script>
        <script type="text/javascript" src="plugins/files/plupload/plupload.html5.js"></script>
        <script type="text/javascript" src="plugins/files/plupload/jquery.plupload.queue/jquery.plupload.queue.js"></script>
        <!-- elFinder initialization (REQUIRED) -->
        <script type="text/javascript">
          var root_path = "<?php echo $_GET['root_path'] ?>";

          var FileBrowserDialogue = {
            init: function() {
              // Here goes your code for setting your custom things onLoad.
              window.parent.tinymce.activeEditor.windowManager.setParams({"lala":"la"});
            },
            mySubmit: function (URL) {
              // pass selected file path to TinyMCE
              console.log(URL);
              window.parent.tinymce.activeEditor.windowManager.getParams().setUrl(URL);

              // close popup window
              window.parent.tinymce.activeEditor.windowManager.close();
            }
          }

          $().ready(function() {
            var elf = $('#elfinder').elfinder({
                // set your elFinder options here
                url: '<?php echo $sistema->system_path; ?>System/includes/connector.php',  // connector URL
                getFileCallback: function(file) { // editor callback
                    // actually file.url - doesnt work for me, but file does. (elfinder 2.0-rc1)
                    arquivo = file.url.split("files/");
                    arquivo = arquivo[(arquivo.length-1)];
                    arquivo = root_path+"files/"+arquivo;

                    FileBrowserDialogue.mySubmit(arquivo); // pass selected file path to TinyMCE 
                }
            }).elfinder('instance');     


            //------------- Plupload php upload  -------------//
            // Setup html4 version
            $("#html4_uploader").pluploadQueue({
                // General settings
                runtimes : 'html5', 
                url : '<?php echo $sistema->system_path; ?>System/includes/upload.php',
                max_file_size : '10mb',
                max_file_count: 15, // user can add no more then 15 files at a time
                chunk_size : '1mb',
                unique_names : false,
                multiple_queues : true,

                // Resize images on clientside if we can
                //resize : {width : 320, height : 240, quality : 80},

                // Rename files by clicking on their titles
                rename: false,

                // Sort files
                sortable: true,

                // Flash settings
                flash_swf_url : '<?php echo $sistema->system_path; ?>plugins/files/plupload/plupload.flash.swf',

                // Specify what files to browse for
                filters : [
                    {title : "Image files", extensions : "jpg,gif,png"},
                    {title : "Zip files", extensions : "zip,avi"}
                ],

                init : {
                    FileUploaded: function(up, file, info) {
                        // Called when all files are either uploaded or failed
                        elf.exec('reload');
                    },
                    UploadComplete: function(up, files) {
                        // Called when all files are either uploaded or failed
                        elf.exec('reload');
                    }
                }

            }); 
          });
        </script>
   
    </body>
</html>

