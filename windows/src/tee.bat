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
var fso = new ActiveXObject("Scripting.FileSystemObject");
var mode=2;
if (WScript.Arguments.Count()==2) {mode=8;}
var logFile = WScript.Arguments(0)
var parentPath = logFile.substring(0, logFile.lastIndexOf("\\"));
if (!fso.FolderExists(parentPath)) {
  fso.CreateFolder(parentPath);
}
var out = fso.OpenTextFile(logFile, mode, true);
var line;
while( !WScript.StdIn.AtEndOfStream ) {
  line=stripAnsi(WScript.StdIn.ReadLine());
  WScript.StdOut.WriteLine(line);
  out.WriteLine(line);
}