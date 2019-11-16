@echo off
title Web documents analysing

:: Encoding of this file set to IBM866 (866) - by default of cmd process
:: Change chcp to UTF-8 (65001) or Windows-1251 (1251) if file was converted
chcp 866 > nul

:: goto :Label - go to label and do code after :Label
:: call :Label - go to label, do code before next label and return
if "%~2"=="" goto :HelpAndExit

set type=%~1
set iterations=%~2
if not exist %type%.txt (
	goto :HelpAndExit
)

Choice /M "Скрипт начнёт анализ страниц .%type% после подтверждения. Продолжить?"
	If ErrorLevel 2 GoTo :No
	If ErrorLevel 1 GoTo :Yes
	Goto :End

:No
	GoTo :End

:Yes
	setlocal enabledelayedexpansion
	set logfile=_output_win_php7_%type%_%iterations%.txt
	set "TAB=	"
	copy nul !logfile! > nul
	:: usebackq - режим обработки кавычек (пока не понадобился)
	:: eol - символ, считается переходом строки, используем как пропуск комментариев
	for /f "usebackq eol=#" %%x in (%type%.txt) do (
		echo ===============
		echo ===============>> !logfile!
		echo %type%
		echo %type%>> !logfile!
		echo ===============
		echo ===============>> !logfile!
		for /r "..\resources\test-docs" %%F in (test_*.%type%) do (
			if exist wrappers\%%x.php (
			    echo ******************************
			    echo ******************************>> !logfile!
			    echo parser:%%x%TAB%file:%%~nxF
			    echo parser:%%x%TAB%file:%%~nxF>> !logfile!

			    call PowerShell -Command Set-ExecutionPolicy RemoteSigned
			    call PowerShell -File ./measure.ps1 wrappers\%%x.php %%F %type% %iterations% >> !logfile!
			)
		)
	)
	endlocal
	echo.
	pause
	goto :End

:HelpAndExit
    echo Usage %~nx0 ^<type: html / xhtml / xml^> ^<number of iterations^>
    pause > nul

:End
    exit /B 0