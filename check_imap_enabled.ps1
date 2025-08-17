# PowerShell script to check if IMAP is enabled
# Run this in PowerShell on Windows or PowerShell Core on Linux

Write-Host "This script will check if IMAP is enabled for a user" -ForegroundColor Yellow
Write-Host "You need to run this on a machine with PowerShell and Exchange Online module" -ForegroundColor Yellow
Write-Host ""
Write-Host "To run this script:" -ForegroundColor Green
Write-Host "1. Open PowerShell as Administrator" -ForegroundColor Green
Write-Host "2. Run: Install-Module -Name ExchangeOnlineManagement" -ForegroundColor Green
Write-Host "3. Run: Connect-ExchangeOnline -UserPrincipalName admin@yourdomain.com" -ForegroundColor Green
Write-Host "4. Run: Get-CASMailbox -Identity 'support@yourdomain.com' | Select ImapEnabled" -ForegroundColor Green
Write-Host ""
Write-Host "If ImapEnabled shows False, run:" -ForegroundColor Yellow
Write-Host "Set-CASMailbox -Identity 'support@yourdomain.com' -ImapEnabled `$true" -ForegroundColor Yellow
