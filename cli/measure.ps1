# File for measure of PHP scripts

$host.ui.RawUI.WindowTitle = "Web documents analysing"

$parser = $args[0]
$file = $args[1]
$type = $args[2]
$iterations = $args[3]

# Process Class:
# https://msdn.microsoft.com/ru-ru/library/system.diagnostics.process(v=vs.110).aspx

function process {
    $si = New-Object System.Diagnostics.ProcessStartInfo
    $si.FileName = "php"
    $si.Arguments = "-f $parser $file $type $iterations"
    $si.RedirectStandardOutput = $true
    $si.UseShellExecute = $false

    $process = New-Object -TypeName System.Diagnostics.Process
    $process.StartInfo = $si

    # this is also valid
    # $process = [System.Diagnostics.Process]::Start($si)

    # Direct result output to | Out-Null
    [void]$process.Start()

    # 2 min
    [int]$maxExecutionTime = (5 * 60) - 1

    [int]$peakWorkingSet = 0
    do {
        $procObj = Get-Process -InputObject $process
        # echo KB: ($procObj.WS / 1024)

        # strange...
        # $process.Refresh()

        # PagedMemorySize64, PeakVirtualMemorySize64, PeakWorkingSet64
        if ($procObj.WS -gt $peakWorkingSet)
        {
           $peakWorkingSet = $procObj.WS
        }

        $current = (get-date)
        $real = ($current - $process.StartTime).TotalSeconds
        if ($real -gt $maxExecutionTime)
        {
        	echo 0
        	echo 0
            $process.Kill()
        }

        Start-Sleep -m 100
    }
    while (!$process.HasExited)


    # $process.WaitForExit()
    # $peakWorkingSet = $process.PeakWorkingSet64

    $real = ($process.ExitTime - $process.StartTime).TotalSeconds

    # $process.CPU = $process.TotalProcessorTime = user + sys
    $user = $process.UserProcessorTime.TotalSeconds
    $sys = $process.PrivilegedProcessorTime.TotalSeconds

    $output = $process.StandardOutput.ReadToEnd()
    $info = "real:$real`tuser:$user`tsys:$sys`tmax RSS:$peakWorkingSet"

    $process.Close()

    $result = [string]::Concat($output, $info)
    Write-Host -ForegroundColor DarkYellow $result
}

process;

# Other variants
<#
$phpRun = 'php -l wrappers\dom.php';
function time($block) {
    $sw = [Diagnostics.Stopwatch]::StartNew()
    $block
    $sw.Stop()
    $sw.Elapsed.TotalSeconds
}
# time(Invoke-Command -ScriptBlock {$phpRun});
time(cmd /c $phpRun);
>#

# Measurement without output...
<#
$res=Measure-Command {Invoke-Command -ScriptBlock {$phpRun}};
echo $res.TotalSeconds;
#>
