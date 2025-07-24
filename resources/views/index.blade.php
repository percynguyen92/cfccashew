<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trạng Thái Hệ Thống</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 40px;
            background-color: #f8fafc;
            color: #2d3748;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table th, .info-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-table th {
            width: 30%;
            font-weight: 600;
        }
        .status-ok {
            color: #38a169; /* Green */
            font-weight: bold;
        }
        .status-error {
            color: #e53e3e; /* Red */
            font-weight: bold;
        }
        .debug-on {
            color: #dd6b20; /* Orange */
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>⚙️ Trạng Thái Hệ Thống</h1>

        <table class="info-table">
            <tbody>
                <tr>
                    <th>Phiên bản Laravel</th>
                    <td>{{ app()->version() }}</td>
                </tr>
                <tr>
                    <th>Phiên bản PHP</th>
                    <td>{{ phpversion() }}</td>
                </tr>
                <tr>
                    <th>Môi trường (Environment)</th>
                    <td>{{ app()->environment() }}</td>
                </tr>
                <tr>
                    <th>Chế độ Gỡ lỗi (Debug Mode)</th>
                    <td>
                        @if(config('app.debug'))
                            <span class="debug-on">Bật</span>
                        @else
                            <span class="status-ok">Tắt</span>
                        @endif
                    </td>
                </tr>
                 <tr>
                    <th>Cache Config</th>
                    <td>
                        @if(app()->configurationIsCached())
                            <span class="status-ok">Đã cache</span>
                        @else
                            <span class="debug-on">Chưa cache</span>
                        @endif
                    </td>
                </tr>
                 <tr>
                    <th>Cache Route</th>
                    <td>
                         @if(app()->routesAreCached())
                            <span class="status-ok">Đã cache</span>
                        @else
                            <span class="debug-on">Chưa cache</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</body>
</html>