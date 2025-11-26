<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Export</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        h1 { margin: 0; color: #333; }
        .meta { color: #777; margin-top: 5px; }
        
        .cards { width: 100%; margin-bottom: 20px; }
        .card { display: inline-block; width: 23%; background: #f4f4f4; padding: 10px; border: 1px solid #ddd; text-align: center; margin-right: 1%; vertical-align: top; }
        .card h3 { margin: 5px 0; font-size: 18px; }
        .card small { text-transform: uppercase; color: #666; font-size: 10px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #333; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 10px; color: white; background: #666; }
        .bg-success { background: #28a745; }
        .bg-warning { background: #ffc107; color: black; }
        .bg-danger { background: #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <h1>@yield('title')</h1>
        <div class="meta">Generated on {{ date('F d, Y H:i') }}</div>
    </div>

    <div class="cards">
        @yield('kpi')
    </div>

    <h3>Detailed Log</h3>
    <table>
        @yield('table')
    </table>
</body>
</html>