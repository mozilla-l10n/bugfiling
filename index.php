<?php

include_once 'controller.inc.php';

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>L10n Bug helper</title>
        <script type="text/javascript" src="<?=$bugzilla_url?>config.cgi"></script>
        <script>
            var selectedProduct;
            function toggleLocales(chk) {
                if (chk.checked == 1) {
                    document.getElementById('locales').style.display = 'none';
                } else {
                    document.getElementById('locales').style.display = '';
                }
            }
            function updateProduct() {
                var blah = document.getElementById('product-select').selectedIndex;
                selectedProduct = document.getElementById('product-select').options[blah].text;
                updateComponent();
                updateVersion();
            }

            function updateComponent() {
                var tempArray = component[selectedProduct];
                var compSelect = document.getElementById('component-select');

                for (var i=0; i<tempArray.length; i++) {
                    var optionItem = document.createElement("option");
                    optionItem.text = tempArray[i];
                    optionItem.value = tempArray[i];
                    compSelect.appendChild(optionItem);
                }
            }
            function updateVersion() {
                var tempArray = version[selectedProduct];
                var verSelect = document.getElementById('version-select');

                for (var i=0; i<tempArray.length; i++) {
                    var optionItem = document.createElement("option");
                    optionItem.text = tempArray[i];
                    optionItem.value = tempArray[i];
                    verSelect.appendChild(optionItem);
                }
            }
        </script>
        <link href="style/main.css" media="all" type="text/css" rel="stylesheet">

    </head>
    <body>

        <div id="main-content">
            <div class="main-input">
                <h2>Bugzilla C3PO</h2>

                <form name="bugzilla" action="bugsfiled.php" method="post">
                    <fieldset>
                        <label class="text">Username:</label><input type="text" name="username" /><br/>
                        <label class="text">Password:</label><input type="password" name="pwd" />
                    </fieldset>

                    <fieldset>
                        <label class="text">Product:</label>
                        <select id="product-select" name="product" onchange="updateProduct()">
                            <option value="null">Select a product</option>
                            <script>
                                for(var product in component) {
                                    document.write('<option value="' + product + '">' + product + '</option>');
                                }
                            </script>
                        </select>
                        <br/>
                        <label class="text">Component:</label>
                        <select id="component-select" name="component">
                            <option value="null">Select component</option>
                        </select>
                        <br/>
                        <label class="text">Version:</label>
                        <select id="version-select" name="version">
                            <option value="null">Select version</option>
                        </select>

                    </fieldset>

                    <fieldset>

                        <label class="text">Assign to:  </label><input type="text" name="assign_to" /><br/>
                        <label class="text">Blocks:     </label><input type="text" name="blocked" /><br/>
                        <label class="text">Whiteboard: </label><input type="text" name="whiteboard" /><br/>
                        <label class="text">URL:        </label><input type="text" name="url" /><br/>
                        <label class="text">List of locales:</label><input id="locales" type="text" name="locales" /><br/>
                        <input onclick="toggleLocales(this);" type="checkbox" name="all-locales" value="all" />
                        <label>All mozilla.org locales</label>
                        <input type="checkbox" name="tag" value="URGENT" />
                        <label>URGENT</label>

                    </fieldset>

                    <fieldset>

                    <label class="text">Summary:</label><input type="text" name="summary" /><br/>
                    <label class="text">Description:</label>
                    <textarea name="description" rows="20" placeholder="Write in your bug description here..." /></textarea>
                    </fieldset>

                    <div class="submit"><input type="submit" value="Submit" /></div>

                </form>
            </div>
        </div>
    </body>


</html>
