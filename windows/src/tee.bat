@if (@X)==(@Y) @end /* Harmless hybrid line that begins a JScript comment

::--- Batch section within JScript comment that calls the internal JScript ----
:: Reference: https://stackoverflow.com/a/10719322
@echo off
cscript //E:JScript //nologo //D "%~f0" %*
exit /b

----- End of JScript comment, beginning of normal JScript  ------------------*/
function stripAnsi(str) {
  return str.replace(/[\u001b\u009b][[()#;?]*(?:[0-9]{1,4}(?:;[0-9]{0,4})*)?[0-9A-ORZcf-nqry=><]/g, '');
}

// Launch a powershell to enable ANSI color support
// Reference: https://www.dostips.com/forum/viewtopic.php?p=59696#p59696
var ps = WScript.CreateObject("WScript.Shell").Exec("powershell.exe -nop -ep Bypass -c \"exit\"");
while (ps.Status == 0) WScript.Sleep(50);

var fso = new ActiveXObject("Scripting.FileSystemObject");
var mode=2;
if (WScript.Arguments.Count()==2) {mode=8;}
var logFile = WScript.Arguments(0)
var parentPath = logFile.substring(0, logFile.lastIndexOf("\\"));
if (!fso.FolderExists(parentPath)) {
  fso.CreateFolder(parentPath);
}
var out = fso.OpenTextFile(logFile, mode, true);
var char;
var line;
while( !WScript.StdIn.AtEndOfStream ) {
  char = "";
  line = "";
  while( char != "\n" ) {
    char=WScript.StdIn.Read(1);
    WScript.StdOut.Write(char);
    line = line + char;
  }
  line=stripAnsi(line);
  out.Write(line);
}