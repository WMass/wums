<html>
<head>
<title><?php echo getcwd(); ?></title>
<style type='text/css'>
body {
    font-family: "Candara", sans-serif;
    font-size: 9pt;
    line-height: 10.5pt;
}
div.pic h3 { 
    font-size: 11pt;
    margin: 0.5em 1em 0.2em 1em;
}
div.pic p {
    font-size: 11pt;
    margin: 0.2em 1em 0.1em 1em;
}
div.pic {
    display: block;
    float: left;
    background-color: white;
    border: 1px solid #ccc;
    padding: 2px;
    text-align: center;
    margin: 2px 10px 10px 2px;
    -moz-box-shadow: 7px 5px 5px rgb(80,80,80);    /* Firefox 3.5 */
    -webkit-box-shadow: 7px 5px 5px rgb(80,80,80); /* Chrome, Safari */
    box-shadow: 7px 5px 5px rgb(80,80,80);         /* New browsers */  
    width: 320px;
}
a { text-decoration: none; color: rgb(80,0,0); }
a:hover { text-decoration: underline; color: rgb(255,80,80); }
div.dirlinks h2 {  margin-bottom: 4pt; margin-left: -24pt; color: rgb(80,0,0);  }
div.dirlinks {  margin: 0 24pt; } 
div.dirlinks a {
    font-size: 11pt; font-weight: bold;
    padding: 0 0.5em; 
}
</style>
</head>
<body>
<h1><?php echo getcwd(); ?></h1>
<?php
$has_subs = true;
foreach (glob("*") as $filename) {
    if (is_dir($filename) && !preg_match("/^\..*|.*private.*/", $filename)) {
        $has_subs = true;
        break;
    }
}
if ($has_subs) {
    print "<div class=\"dirlinks\">\n";
    print "<h2>Directories</h2>\n";
    print "<a href=\"../\">[parent]</a> ";
    foreach (glob("*") as $filename) {
        if (is_dir($filename) && ($_SERVER['PHP_AUTH_USER'] == 'gpetrucc' || !preg_match("/^\..*|.*private.*/", $filename))) {
            print " <a href=\"$filename\">[$filename]</a>";
        }
    }
    print "</div>";
}

foreach (array("00_README.txt", "README.txt", "readme.txt") as $readme) {
    if (file_exists($readme)) {
        print "<pre class='readme'>\n"; readfile($readme); print "</pre>";
    }
}
?>

<h2><a name="plots">Plots</a></h2>
<p><form>Filter: <input type="text" name="match" size="30" value="<?php if (isset($_GET['match'])) print htmlspecialchars($_GET['match']);  ?>" /><input type="Submit" value="Go" /><input type="checkbox"  name="regexp" <?php if ($_GET['regexp']) print "checked=\"checked\""?> >RegExp</input></form></p>
<div>
<?php
$displayed = array();
if ($_GET['noplots']) {
    print "Plots will not be displayed.\n";
} else {
  $other_exts = array('.pdf', '.cxx', '.eps', '.root', '.txt', '.log', '.dir', '.info', '.latex', '.html');
    $filenames = glob("*.png"); sort($filenames);
    foreach ($filenames as $filename) {
        if (isset($_GET['match'])) {
             if (isset($_GET['regexp']) && $_GET['regexp']) {
                if (!preg_match('/.*'.$_GET['match'].'.*/', $filename)) continue;
             } else {
                if (!fnmatch('*'.$_GET['match'].'*', $filename)) continue;
             }
        }
        array_push($displayed, $filename);
        $brfname = str_replace("_","_&shy;",$filename);
        print "<div class='pic'>\n";
        print "<h3><a href=\"$filename\">$brfname</a></h3>";
        print "<a href=\"$filename\"><img src=\"$filename\" style=\"border: none; width: 300px; \"></a>";
        $others = array();
        foreach ($other_exts as $ex) {
            $other_filename = str_replace('.png', $ex, $filename);
            if (file_exists($other_filename)) {
                array_push($others, "<a class=\"file\" href=\"$other_filename\">[" . $ex . "]</a>");
                if ($ex != '.txt') array_push($displayed, $other_filename);
            }
        }
        if ($others) print "<p>Also as ".implode(', ',$others)."</p>";
        print "</div>";
    }
}
?>
</div>
<div style="display: block; clear:both;">
<h2><a name="files">Other files</a></h2>
<ul>
<?php
foreach (glob("*") as $filename) {
    if ($_GET['noplots'] || !in_array($filename, $displayed)) {
        if (isset($_GET['match'])) {
             if (isset($_GET['regexp']) && $_GET['regexp']) {
                if (!preg_match('/.*'.$_GET['match'].'.*/', $filename)) continue;
             } else {
                if (!fnmatch('*'.$_GET['match'].'*', $filename)) continue;
             }
        }
        if (is_dir($filename)) {
            print "<li>[DIR] <a href=\"$filename\">$filename</a></li>";
        } else {
            print "<li><a href=\"$filename\">$filename</a></li>";
        }
    }
}
?>
</ul>
</div>
<div id="lb" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:1000; text-align:center;">
  <div id="lb-cap" style="color:#eee; font-size:12pt; padding:8px;"></div>
  <img id="lb-img" style="max-width:96vw; max-height:calc(100vh - 50px); background:white;">
</div>
<script>
// Lightbox: click a plot to enlarge; left/right arrows step through plots in page order, Esc closes.
(function() {
    var pics = Array.prototype.map.call(document.querySelectorAll("div.pic > a > img"), function(im) { return im.parentNode.getAttribute("href"); });
    var lb = document.getElementById("lb"), img = document.getElementById("lb-img"), cap = document.getElementById("lb-cap");
    var cur = -1;
    function show(i) {
        cur = (i + pics.length) % pics.length;
        img.src = pics[cur];
        cap.innerHTML = '<a style="color:#fcc" href="' + pics[cur] + '">' + pics[cur] + '</a> &nbsp; (' + (cur + 1) + '/' + pics.length + ') &nbsp; &larr; &rarr; to navigate, Esc to close';
        lb.style.display = "block";
        history.replaceState(null, "", "#" + encodeURIComponent(pics[cur]));
    }
    function hide() {
        lb.style.display = "none"; cur = -1;
        history.replaceState(null, "", location.pathname + location.search);
    }
    document.querySelectorAll("div.pic a").forEach(function(a) {
        var i = pics.indexOf(a.getAttribute("href"));
        if (i < 0) return;
        a.addEventListener("click", function(e) {
            if (e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;  // keep open-in-new-tab
            e.preventDefault(); show(i);
        });
    });
    lb.addEventListener("click", function(e) { if (e.target === lb) hide(); });
    document.addEventListener("keydown", function(e) {
        if (cur < 0) return;
        if (e.key === "ArrowRight" || e.key === "ArrowDown") { show(cur + 1); e.preventDefault(); }
        else if (e.key === "ArrowLeft" || e.key === "ArrowUp") { show(cur - 1); e.preventDefault(); }
        else if (e.key === "Escape") hide();
    });
    var h = decodeURIComponent(location.hash.slice(1));
    if (h && pics.indexOf(h) >= 0) show(pics.indexOf(h));
})();
</script>
</body>
</html>
