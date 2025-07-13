$body = @{
    test = "data"
} | ConvertTo-Json

$response = Invoke-WebRequest -Uri "https://coparente.top/chat/webhook/serpro" -Method POST -ContentType "application/json" -Body $body

Write-Host "Status: $($response.StatusCode)"
Write-Host "Content: $($response.Content)" 