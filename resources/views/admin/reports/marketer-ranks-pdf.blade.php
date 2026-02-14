<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="utf-8">
    <title>تقرير رتب المسوقين</title>
    <style>
        body {
            font-family: 'XITS', 'DejaVu Sans', sans-serif;
            font-size: 12px;
            direction: rtl;
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #eee;
            padding: 10px;
            text-align: right;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #999;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>تقرير رتب المسوقين</h1>
        <p>تاريخ الاستخراج: {{ date('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>المسوق</th>
                <th>رقم الهاتف</th>
                <th>الرتبة</th>
                <th>المعامل</th>
                <th>عدد العملاء</th>
                <th>تاريخ التسجيل</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}<br><small>{{ $user->email }}</small></td>
                <td>{{ $user->phone ?: '-' }}</td>
                <td>{{ $user->getRankLabel() }}</td>
                <td>{{ number_format($user->commission_multiplier, 2) }}x</td>
                <td>{{ $user->leads_count }}</td>
                <td>{{ $user->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        تم استخراج هذا التقرير من نظام نسبة - {{ date('Y') }}
    </div>
</body>

</html>