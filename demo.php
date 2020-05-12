<?php
include_once("config.php");
require_once 'vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$file = '';
?>

<!DOCTYPE html>
<html lang="en">
<?php
include_once("head.php");

$file = '';
?>

<body id="page-top" class="index">
    <nav id="mainNav" class="navbar navbar-default navbar-custom navbar-fixed-top">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header page-scroll">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span> Menu <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand page-scroll" href="#page-top">Home</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav navbar-right">
                    <li class="hidden">
                        <a href="#page-top"></a>
                    </li>
                   <li class="">
                       <a href="index.php" class="">Introduction</a>
                   </li>
                    <li>
                        <a class="active" href="#contact">Demo</a>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>
    <section id="allmethods">
        <div class="col-md-12" style="padding-top: 55px">
            <div class="intro-heading alert alert-info text-center" id="conversionofjsondatatoodpfile" ><strong>Test the Microservice</strong></div>
        </div>
        <div class="container-fluid align-center">
            <div class="row">
                <div class="col-md-12">
                    <div class="tabbable-panel">
                        <div class="tabbable-line">
                            <ul class="nav nav-tabs" style="text-align: center;">
                                <li class="active" style="float: none;display: inline-block;">
                                    <a href="#convert" id="convert1" aria-controls="home" role="tab" data-toggle="tab" >Test with Sample Data</a>
                                </li>
                                <li style="float: none;display: inline-block;">
                                    <a href="#healthcheck" id="healthcheck1" aria-controls="profile" role="tab" data-toggle="tab" formaction="./api/healthcheck">Health Check</a>
                                </li>
                                <li style="float: none;display: inline-block;">
                                    <a href="#checklogs" id="checklogs1" aria-controls="settings" role="tab" data-toggle="tab">Check Logs</a>
                                </li>
                                <li style="float: none;display: inline-block;">
                                    <a href="#backups" id="backups1" aria-controls="settings" role="tab" data-toggle="tab">Backup</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="convert">
                                    <div class="container" id="showdata">
                                        <div class="header-inner">
                                            <form action="./api/convert" method="post">
                                                <div class="col-md-3">
                                                    <div class="header-inner">
                                                        <select id="sample_data" class="form-control" name="sample"  onchange="MySampleData()">
                                                            <option value="">-- Select Sample Data --</option>
                                                            <?php

                                                            foreach (glob('./sample_data/*.js') as $filename) {
                                                                ?>  
                                                                <option value=<?php echo str_replace("./sample_data/", '', $filename) ?> ><?php echo str_replace('./sample_data/', '', $filename); ?></option>
                                                            <?php
                                                            }
                                                            ?>
                                                        </select> 
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="header-inner">
                                                        <select name="template"  class="form-control">
                                                            <?php
                                                            foreach (array_diff(scandir(getenv('ODP_DIR')), array('.','..')) as $value) {
                                                                echo "<option value='$value'> $value\n";
                                                            }
                                                            ?>
                                                        </select> 
                                                    </div>
                                                </div>
                                                <div class="col-md-3 last">
                                                    <div class="header-inner"> 
                                                       <select name="format" class="form-control">
                                                            <option value="">-- Select Format --</option>
                                                                
                                                            <option value="odp" >odp </option>
                                                            <option value="pptx" >pptx </option>
                                                            <option value="pdf" >pdf </option>
                                                        </select> 
                                                    </div>
                                                </div>
                                                <div class="clearfix"></div>
                                                <div class="container_form">
                                                    <textarea name="data" rows="20" id="data" class="form-control"><?php echo $file ; ?></textarea>
                                                        <br>
                                                    <input type="submit" value="Submit to Test" class="btn btn-success  btn-lg">
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="healthcheck">
                                    
                                </div>
                                <div class="tab-pane" id="checklogs">
                                    
                                </div>
                                <div class="tab-pane" id="backups">
                                  
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script type="text/javascript">
        function MySampleData()
        {
            var myFile = document.getElementById("sample_data").value; 
            var sample1 = '';
            $.getJSON( './sample_data/' + myFile + '?t=' + Math.random(), function( data ) {
                $("#data").val(JSON.stringify(data, undefined, 3));
            });

        }

        function pageRedirect(page_url) {
            window.location.href = page_url;
        } 

        $(document).ready(function(){
            $('#showdata').hide();
            
            $("#convert1").click(function(){
                $('#showdata').show();
            });

            $("#healthcheck1").click(function(){
                page_url = "./api/healthcheck";
                pageRedirect(page_url);
            });

            $("#backups1").click(function(){
                page_url = "./api/backups";
                pageRedirect(page_url);
            });

            $("#checklogs1").click(function(){
                page_url = "./api/logs";
                pageRedirect(page_url);
            });

        });
    </script>
  <?php
include_once("footer.php");

$file = '';
?>
</body>
</html>
