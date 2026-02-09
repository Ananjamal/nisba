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
            font-size: 12px;
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
            font-size: 12px;
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
        <h2>تقرير العملاء</h2>
        <p>تاريخ التقرير: {{ date('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>اسم العميل</th>
                <th>الشركة</th>
                <th>المدينة</th>
                <th>رقم الهاتف</th>
                <th>حالة العميل</th>
                <th>المسوقون</th>
                <th>تاريخ الإضافة</th>
            </tr>
        </thead>
        <tbody>
            @foreach($leads as $lead)
            <tr>
                <td>{{ $lead->id }}</td>
                <td>{{ $lead->client_name }}</td>
                <td>{{ $lead->company_name }}</td>
                <td>{{ $lead->city }}</td>
                <td>{{ $lead->client_phone }}</td>
                <td>
                    @switch($lead->status)
                    @case('under_review') تحت المراجعة @break
                    @case('contacting') جاري التواصل @break
                    @case('sold') تم البيع @break
                    @case('cancelled') ملغي @break
                    @default {{ $lead->status }}
                    @endswitch
                </td>
                <td>{{ $lead->users->pluck('name')->implode(', ') }}</td>
                <td>{{ $lead->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        تم استخراج هذا التقرير من نظام حليف
    </div>
</body>

</html>