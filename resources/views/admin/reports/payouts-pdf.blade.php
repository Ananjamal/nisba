<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: right;
            font-size: 11px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
            color: #333;
        }

        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 11px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #999;
            padding: 10px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>تقرير طلبات السحب (التحويلات البنكية)</h2>
        <p>تاريخ التقرير: {{ date('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>المسوق</th>
                <th>العميل</th>
                <th>المبلغ</th>
                <th>الحالة</th>
                <th>البنك</th>
                <th>الآيبان</th>
                <th>تاريخ الطلب</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payouts as $payout)
            <tr>
                <td>{{ $payout->id }}</td>
                <td>{{ $payout->user->name }}</td>
                <td>{{ $payout->client_name ?: ($payout->lead ? $payout->lead->client_name : '-') }}</td>
                <td>{{ number_format($payout->amount, 2) }} ر.س</td>
                <td>
                    @switch($payout->status)
                    @case('pending') قيد الانتظار @break
                    @case('paid') تم الدفع @break
                    @case('cancelled') ملغي @break
                    @default {{ $payout->status }}
                    @endswitch
                </td>
                <td>{{ $payout->bank_name }}</td>
                <td><small>{{ $payout->iban }}</small></td>
                <td>{{ $payout->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        تم استخراج هذا التقرير من نظام حليف
    </div>
</body>

</html>