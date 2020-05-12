# ExportLandscape
## Convert a JSON Encoded CI Landscape in .pptx/.pdf format

# About ExportLandscape
The ExportLandscape Microservice is a part of the Integrate Edge Competitive Intelligence Portal® focused towards the Pharma Sector.

Integrate Edge is a web based application. It has multiple modules related to News, Earning Calls, Promotional Materials, Competitive Landscapes, Trial Timelines, and others. It is primarily a visualisation software, and is used to convey information from multiple sources through an integrated interface.

The module *Competitive Landscape*, renders a stacked list of products, segregated by Phases. There are multiple filters through which a user can see the entire competitive scenario for their product/indication. More often than not, the users require an export of the information in a suitable format so that they can include it in their own documents and presentations. Currently the Competitive Landscape module supports an export of the landscape in a non-editable image format.

The purpose of ExportLandscape service is to use a JSON encoded landscape data from Integrate Edge, and convert it into an **Editable** presentation. For now, it can export the landscape in .pptx, .pdf and .odp formats.

# How to use the service

Currently the service supports 4 methods, `convert`, `healthcheck`, `logs`, and `backups`. Only the first method `convert` is fulfilling the core purpose, and the other 3 methods are part of the overall microservices architecture implemented for Integrate Edge.

To use the service, you can visit the Microservices Link, and connect with the relevant version. For example:

```
http://<domain-name>/export/api/convert
```

You will receive the data either in a JSON encoded format, or as a binary (in case of a file export).


# Methods

## convert

Reference Link

```
http://<domain-name>/export/api/convert
```

The `convert` Method is the base of the entire functionality provided by ExportLandscape. It takes in the data provided by the Competitive Landscape module, and processes it to generate the desired output file from it. 

You have to generate a `POST` request using the following parameters and schema:

1. `template` - Template Name, as defined in `config.php` -- "Default" for Generic template
1. `format` - Select between `odp`, `pdf` & `pptx` -- default "pptx"
1. `filename` - Prefix of the File which gets downloaded -- "Generic_" for Generic_19012019.pptx
1. `responseType` - Select between `filepath`, & `stream` -- default "filepath"
1. `data` - JSON encoded data with the schema below:

```
{
    "altTitle": "Data:+06+Jun+2019",
    "show_flag": "N",
    "has_phase_arrow": "N",
    "landscapeName": "Cell+Therapy+Technologies+Landscape",
    "sheetName": "Competitive+Landscape",
    "displayKey": ["name", "originator_company", "licensee_company"],
    "iconsKey": ["product_inhibitors", "product_doubtful", "product_type"],
    "colorsKey": ["originator_company"],
    "phases": {
        "Cell+Collection": {
            "priority": "1",
            "assets": [{
                "id": "19",
                "brand_name": "Spectra+Optia+Apheresis+System",
                "name": "Spectra+Optia+Apheresis+System",
                "originator_company": ["Terumo+BCT"],
                "licensee_company": [],
                "colors": {
                    "Terumo+BCT": "#1432c3"
                }
            }],
            "color": {
                "background": "#333333",
                "color": "#ffffff"
            }
        },
        "Cell+Isolation": {
            "priority": "2",
            "assets": [{
                "id": "6",
                "brand_name": "Cocoon+GMP+Technology+System",
                "name": "Cocoon+GMP+Technology+System",
                "originator_company": ["Lonza"],
                "licensee_company": [],
                "product_type": [{
                    "position": "top-right",
                    "name": "Pipeline",
                    "iconPath": "<domain-name>/assets/2ed88ffb/images/terumo/pipeline.png"
                }],
                "colors": {
                    "Lonza": "#bd0c95"
                }
            }, {
                "id": "14",
                "brand_name": "Elutra+Cell+Separation+System",
                "name": "Elutra+Cell+Separation+System",
                "originator_company": ["Terumo+BCT"],
                "licensee_company": [],
                "colors": {
                    "Terumo+BCT": "#1432c3"
                }
            }, {
                "id": "16",
                "brand_name": "COBE+2991+Cell+Processor",
                "name": "COBE+2991+Cell+Processor",
                "originator_company": ["Terumo+BCT"],
                "licensee_company": [],
                "colors": {
                    "Terumo+BCT": "#1432c3"
                }
            }, {
                "id": "17",
                "brand_name": "Sepax+C-Pro+Cell+Processing+System",
                "name": "Sepax+C-Pro+Cell+Processing+System",
                "originator_company": ["GE+Lifesciences"],
                "licensee_company": [],
                "colors": {
                    "GE+Lifesciences": "#346029"
                }
            }, {
                "id": "3",
                "brand_name": "CliniMACS+Prodigy",
                "name": "CliniMACS+Prodigy",
                "originator_company": ["Miltenyi+Biotec"],
                "licensee_company": [],
                "colors": {
                    "Miltenyi+Biotec": "#342494"
                }
            }, {
                "id": "4",
                "brand_name": "CliniMACS+Plus+Instrument",
                "name": "CliniMACS+Plus+Instrument",
                "originator_company": ["Miltenyi+Biotec"],
                "licensee_company": [],
                "colors": {
                    "Miltenyi+Biotec": "#342494"
                }
            }],
            "color": {
                "background": "#333333",
                "color": "#ffffff"
            }
        },
        "Cell+&+Gene+Modification": {
            "priority": "3",
            "assets": [{
                "id": "7",
                "brand_name": "4D-Nucleofector+System",
                "name": "4D-Nucleofector+System",
                "originator_company": ["Lonza"],
                "licensee_company": [],
                "colors": {
                    "Lonza": "#bd0c95"
                }
            }],
            "color": {
                "background": "#333333",
                "color": "#ffffff"
            }
        },
        "Cell+Proliferation+&+Expansion": {
            "priority": "4",
            "assets": [{
                "id": "6",
                "brand_name": "Cocoon+GMP+Technology+System",
                "name": "Cocoon+GMP+Technology+System",
                "originator_company": ["Lonza"],
                "licensee_company": [],
                "product_type": [{
                    "position": "top-right",
                    "name": "Pipeline",
                    "iconPath": "<domain-name>/assets/2ed88ffb/images/terumo/pipeline.png"
                }],
                "colors": {
                    "Lonza": "#bd0c95"
                }
            }, {
                "id": "15",
                "brand_name": "Quantum+Cell+Expansion+System",
                "name": "Quantum+Cell+Expansion+System",
                "originator_company": ["Terumo+BCT"],
                "licensee_company": [],
                "colors": {
                    "Terumo+BCT": "#1432c3"
                }
            }, {
                "id": "3",
                "brand_name": "CliniMACS+Prodigy",
                "name": "CliniMACS+Prodigy",
                "originator_company": ["Miltenyi+Biotec"],
                "licensee_company": [],
                "developmentphase_comment": "",
                "colors": {
                    "Miltenyi+Biotec": "#342494"
                }
            }],
            "color": {
                "background": "#333333",
                "color": "#ffffff"
            }
        },
        "Cell+Washing+&+Concentration": {
            "priority": "5",
            "assets": [{
                "id": "13",
                "brand_name": "ACP215+Automated+Cell+Processor",
                "name": "ACP215+Automated+Cell+Processor",
                "originator_company": ["Haemonetics"],
                "licensee_company": [],
                "colors": {
                    "Haemonetics": "#744500"
                }
            }, {
                "id": "16",
                "brand_name": "COBE+2991+Cell+Processor",
                "name": "COBE+2991+Cell+Processor",
                "originator_company": ["Terumo+BCT"],
                "licensee_company": [],
                "colors": {
                    "Terumo+BCT": "#1432c3"
                }
            }, {
                "id": "17",
                "brand_name": "Sepax+C-Pro+Cell+Processing+System",
                "name": "Sepax+C-Pro+Cell+Processing+System",
                "originator_company": ["GE+Lifesciences"],
                "licensee_company": [],
                "colors": {
                    "GE+Lifesciences": "#346029"
                }
            }, {
                "id": "3",
                "brand_name": "CliniMACS+Prodigy",
                "name": "CliniMACS+Prodigy",
                "originator_company": ["Miltenyi+Biotec"],
                "licensee_company": [],
                "colors": {
                    "Miltenyi+Biotec": "#342494"
                }
            }, {
                "id": "5",
                "brand_name": "Lovo+Cell+Processing+System",
                "name": "Lovo+Cell+Processing+System",
                "originator_company": ["Fresenius+Kabi"],
                "licensee_company": [],
                "colors": {
                    "Fresenius+Kabi": "#3e0204"
                }
            }],
            "color": {
                "background": "#333333",
                "color": "#ffffff"
            }
        },
        "Formulation+(Fill+&+Finish)": {
            "priority": "6",
            "assets": [{
                "id": "3",
                "brand_name": "CliniMACS+Prodigy",
                "name": "CliniMACS+Prodigy",
                "originator_company": ["Miltenyi+Biotec"],
                "licensee_company": [],
                "colors": {
                    "Miltenyi+Biotec": "#342494"
                }
            }],
            "color": {
                "background": "#333333",
                "color": "#ffffff"
            }
        },
        "Cryo-preservation": {
            "priority": "7",
            "assets": [{
                "id": "20",
                "brand_name": "VIA+Freeze+Controlled-Rate+Freezer",
                "name": "VIA+Freeze+Controlled-Rate+Freezer",
                "originator_company": ["GE+Lifesciences"],
                "licensee_company": [],
                "colors": {
                    "GE+Lifesciences": "#346029"
                }
            }],
            "color": {
                "background": "#333333",
                "color": "#ffffff"
            }
        },
        "Data+Management+Software": {
            "priority": "9",
            "assets": [{
                "id": "18",
                "brand_name": "Personalized+Therapy+Management+Software",
                "name": "Personalized+Therapy+Management+Software",
                "originator_company": ["Vineti"],
                "licensee_company": [],
                "colors": {
                    "Vineti": "#4037a6"
                }
            }],
            "color": {
                "background": "#333333",
                "color": "#ffffff"
            }
        }
    },
    "legends": {
        "colors": {
            "Lonza": "#bd0c95",
            "Haemonetics": "#744500",
            "Terumo+BCT": "#1432c3",
            "GE+Lifesciences": "#346029",
            "Vineti": "#4037a6",
            "Miltenyi+Biotec": "#342494",
            "Fresenius+Kabi": "#3e0204"
        },
        "icons": {
            "addClass@@pipeline": "<domain-name>/assets/2ed88ffb/images/terumo/pipeline.png"
        }
    },
    "legendsClassificationArr": {
        "classification": {
            "colors": {
                "Lonza": "#bd0c95",
                "Haemonetics": "#744500",
                "Terumo+BCT": "#1432c3",
                "GE+Lifesciences": "#346029",
                "Vineti": "#4037a6",
                "Miltenyi+Biotec": "#342494",
                "Fresenius+Kabi": "#3e0204"
            },
            "inhibitors": [],
            "status": {
                "addClass@@pipeline": "<domain-name>/assets/2ed88ffb/images/terumo/pipeline.png"
            }
        }
    },
    "subfilters": {
        "1": "Fresenius+Kabi",
        "2": "Miltenyi+Biotec",
        "3": "Lonza",
        "10": "GE+Lifesciences",
        "11": "Vineti",
        "14": "Haemonetics",
        "15": "Terumo+BCT"
    },
    "subfilter_type": "originator_company",
    "filter": "",
    "viewMode": "processes",
    "product_type": "marketed"
}
```

#### Healthcheck
[Link](https://$directory_path/$hostname/api/healthcheck)
e.g. `http://<domain-name>/export/api/healthcheck` 

Health check method is call when user user choose the format of output. for example if he choose .ppt file , this method will check is there any software that can open .ppt file in the user machine.


#### Logs
[Link](https://$directory_path/$hostname/api/logs)
e.g. `http://<domain-name>/export/api/logs`

This method has all the logs. i.e logs about the user, data provided etc..
Event logs provide historical information that can help you track down system and security problems. 
When this service is started, you can track user actions and system resource usage events with the following event logs:

1. Application Log Records events logged by applications, such as the failure of a database.
2. Directory Service Records events logged by Active Directory and its related services.
3. DNS Server Records DNS queries, responses, and other DNS activities.
4. Security Log Records events you've set for auditing with local or global group policies.


#### Backup
[Link](https://$directory_path/$hostname/api/backups/)
e.g. `http://<domain-name>/export/api/backups`

# Installation & Configuration

* Pull the project from the repository based on the version tag.
* Modify the `config.php` values.
* Assign web server user privileges (`www-data`, `_www` or `httpd`) to `base_odp_template/<CLIENT_NAME>/content.xml` & `base_odp_template/<CLIENT_NAME>/content.xml`.
* Libreoffice should be installed on the server.
* Make two folders in the main export directory(`.cache` and `.config`).This is must for coverting one file format to other.
* Please make sure the web user has the previlages of the web directory(`e.g /var/www`).
* Enable Apache Module mod_rewrite.
* Allow overwrite permission to .htaccess.

# ChangeLog 

**v0.5** 26 Dec 2017.
* This version contains the basic functionality of converting json data of landscape into required format i.e pptx,odp or pdf.It also contains User Interface for checking the working of the service.
* Only the convert service is implemented in this version.

**v3.00** 25 April 2018
* The rendering of the products is changed from vertical order to horizontal.
* The moa's are rendered on the same slide/page and are dynamic according to the products on the slide.
* The length of the phasebar is adjusted to the whitespace available on the slide/page.
* The title of the slides is coming from the data provided to the service rather than being static.

# Sample code for Testing the service
* Use the code below, make a file and change the url according to the location of the file.

```
<html>
<head>
  <title>
     Testing of Download via Ajax
  </title>
<script src="https://code.jquery.com/jquery-1.11.3.js"></script>
</head>
  <body>
    <h3>Testing of Download Functionality</h3>
    <input type="button" onclick="download()" value="Download Testing">
    <form  action="http://localhost/export/api/convert" method="post" target="hidden-form" id="myform">
    <input type="hidden" name="template" id="template" value="">
    <input type="hidden" name="format" id="format" value="">
    <input type="hidden" name="data" id="data" value=''>
    </form>
    <IFRAME style="display:none" name="hidden-form"></IFRAME> 
    <script>
      function download() {
        var template = "Default Template";
        var format = ".odp";
        var data = $("#jsondata").val();

        $("#template").val(template);
        $("#format").val(format);
        $("#data").val(data);
        $("#myform").submit();
      }
    </script>
    <input id="jsondata" value='<?php
    echo file_get_contents("../export/sample_data/Landscape_Sample_Data.js");
    ?>'>
  </body>
</html>
```