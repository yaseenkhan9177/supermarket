<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Voucher — {{ $expense->expense_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #1e293b; background: #fff; }
        .voucher { max-width: 700px; margin: 20px auto; padding: 32px; border: 2px solid #e2e8f0; border-radius: 12px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #ef4444; padding-bottom: 16px; margin-bottom: 20px; }
        .company-name { font-size: 20px; font-weight: 900; color: #1e293b; }
        .company-sub { font-size: 11px; color: #64748b; margin-top: 3px; }
        .voucher-title { font-size: 18px; font-weight: 900; color: #ef4444; text-align: right; }
        .voucher-no { font-size: 11px; color: #64748b; text-align: right; margin-top: 3px; font-family: monospace; }
        .amount-box { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border-radius: 10px; padding: 20px 24px; text-align: center; margin: 20px 0; }
        .amount-label { font-size: 11px; font-weight: 600; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.05em; }
        .amount-value { font-size: 32px; font-weight: 900; margin-top: 4px; letter-spacing: -0.02em; }
        .amount-words { font-size: 11px; opacity: 0.85; margin-top: 4px; }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 24px; margin: 20px 0; }
        .detail-item { border-bottom: 1px dashed #e2e8f0; padding-bottom: 8px; }
        .detail-label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }
        .detail-value { font-size: 12px; font-weight: 600; color: #1e293b; margin-top: 2px; }
        .description-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin: 16px 0; }
        .description-label { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
        .description-text { font-size: 13px; color: #334155; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; margin-top: 40px; padding-top: 16px; border-top: 1px solid #e2e8f0; }
        .sig-box { text-align: center; }
        .sig-line { border-bottom: 1px solid #1e293b; margin-bottom: 6px; height: 32px; }
        .sig-label { font-size: 10px; color: #64748b; font-weight: 600; }
        .footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #e2e8f0; text-align: center; color: #94a3b8; font-size: 10px; }
        @media print {
            body { background: #fff; }
            .voucher { margin: 0; border: none; border-radius: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="background:#1e293b;padding:12px 20px;display:flex;gap:10px;align-items:center;">
    <button onclick="window.print()" style="background:#ef4444;color:#fff;border:none;padding:8px 18px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">
        🖨 Print Voucher
    </button>
    <a href="{{ route('expenses.show', $expense->id) }}" style="color:#94a3b8;font-size:12px;text-decoration:none;">← Back to Detail</a>
</div>

<div class="voucher">
    <div class="header">
        <div>
            <div class="company-name">{{ auth()->user()->store->store_name ?? config('app.name') }}</div>
            <div class="company-sub">{{ auth()->user()->store->address ?? '' }}</div>
        </div>
        <div>
            <div class="voucher-title">EXPENSE VOUCHER</div>
            <div class="voucher-no">{{ $expense->expense_no }}</div>
            <div class="voucher-no">Date: {{ $expense->expense_date->format('d-M-Y') }}</div>
        </div>
    </div>

    <div class="amount-box">
        <div class="amount-label">Total Amount Paid</div>
        <div class="amount-value">Rs. {{ number_format($expense->amount, 2) }}</div>
        <div class="amount-words">{{ $expense->category_name }} • {{ $expense->payment_method }}</div>
    </div>

    <div class="description-box">
        <div class="description-label">Description</div>
        <div class="description-text">{{ $expense->description }}</div>
        @if($expense->notes)
        <div style="color:#64748b;font-size:11px;margin-top:4px;">Note: {{ $expense->notes }}</div>
        @endif
    </div>

    <div class="details-grid">
        <div class="detail-item">
            <div class="detail-label">Expense Category</div>
            <div class="detail-value">{{ $expense->category_name }}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Payment Method</div>
            <div class="detail-value">{{ $expense->payment_method }}</div>
        </div>
        @if($expense->wallet)
        <div class="detail-item">
            <div class="detail-label">Paid From Account</div>
            <div class="detail-value">{{ $expense->wallet->name }}</div>
        </div>
        @endif
        @if($expense->reference_no)
        <div class="detail-item">
            <div class="detail-label">Reference / Cheque #</div>
            <div class="detail-value" style="font-family:monospace;">{{ $expense->reference_no }}</div>
        </div>
        @endif
        <div class="detail-item">
            <div class="detail-label">Prepared By</div>
            <div class="detail-value">{{ $expense->user->name ?? 'Staff' }}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Recorded On</div>
            <div class="detail-value">{{ $expense->created_at->format('d-M-Y, h:i A') }}</div>
        </div>
    </div>

    <div class="signatures">
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">Prepared By</div>
        </div>
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">Verified By</div>
        </div>
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">Approved By</div>
        </div>
    </div>

    <div class="footer">
        This is a computer-generated voucher. — {{ config('app.name') }} — {{ now()->format('d M Y, h:i A') }}
    </div>
</div>
</body>
</html>
