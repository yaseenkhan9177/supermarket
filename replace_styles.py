import re

def update_file(filename, is_debit=False):
    with open(filename, 'r') as f:
        content = f.read()

    # 1. Update the grid gap
    content = content.replace(
        '<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 mb-6">',
        '<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 mb-8">'
    )

    # 2. Update Customer Card
    old_customer_card_cash = '''<div class="bg-slate-900 rounded-xl p-4 lg:p-5 border border-slate-800">
            <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                <i class="fas fa-user text-blue-500"></i> Customer
            </h3>'''
    old_customer_card_debit = '''<div class="bg-slate-900 rounded-xl p-4 lg:p-5 border border-red-900/50 shadow-lg shadow-red-900/10">
            <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                <i class="fas fa-user text-red-500"></i> Customer (Required)
            </h3>'''
    
    new_customer_card = f'''<div class="bg-slate-900 rounded-2xl shadow-xl border {'border-red-900/50 shadow-red-900/10' if is_debit else 'border-slate-800'} p-6 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-32 h-32 {'bg-red-500/10 group-hover:bg-red-500/20' if is_debit else 'bg-blue-500/10 group-hover:bg-blue-500/20'} rounded-full blur-2xl transition duration-300"></div>
            <h3 class="text-white text-lg font-bold mb-5 flex items-center gap-3 relative z-10">
                <div class="w-8 h-8 rounded-lg {'bg-red-500/20 text-red-500 border border-red-500/30' if is_debit else 'bg-blue-500/20 text-blue-500 border border-blue-500/30'} flex items-center justify-center shadow-sm">
                    <i class="fas fa-user text-sm"></i>
                </div>
                Customer{ " (Required)" if is_debit else ""}
            </h3>'''
    
    content = content.replace(old_customer_card_debit if is_debit else old_customer_card_cash, new_customer_card)

    # 3. Update Invoice Card
    old_invoice_card = '''<div class="bg-slate-900 rounded-xl p-4 lg:p-5 border border-slate-800">
            <h3 class="text-white font-bold mb-4">
                <i class="fas fa-file-invoice text-green-500"></i> Invoice
            </h3>'''
    old_invoice_card_debit = '''<div class="bg-slate-900 rounded-xl p-4 lg:p-5 border border-slate-800">
            <h3 class="text-white font-bold mb-4">
                <i class="fas fa-file-invoice text-slate-400"></i> Invoice Details
            </h3>'''

    new_invoice_card = f'''<div class="bg-slate-900 rounded-2xl shadow-xl border border-slate-800 p-6 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-32 h-32 {'bg-slate-500/10 group-hover:bg-slate-500/20' if is_debit else 'bg-green-500/10 group-hover:bg-green-500/20'} rounded-full blur-2xl transition duration-300"></div>
            <h3 class="text-white text-lg font-bold mb-5 flex items-center gap-3 relative z-10">
                <div class="w-8 h-8 rounded-lg {'bg-slate-500/20 text-slate-400 border border-slate-500/30' if is_debit else 'bg-green-500/20 text-green-500 border border-green-500/30'} flex items-center justify-center shadow-sm">
                    <i class="fas fa-file-invoice text-sm"></i>
                </div>
                Invoice{ " Details" if is_debit else ""}
            </h3>'''
    
    content = content.replace(old_invoice_card_debit if is_debit else old_invoice_card, new_invoice_card)

    # 4. Update Payment Card
    old_payment_card_cash = '''<div class="bg-slate-900 rounded-xl p-4 lg:p-5 border border-slate-800 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-green-500/20 to-transparent rounded-bl-full"></div>
            <h3 class="text-white font-bold mb-2 relative z-10">
                <i class="fas fa-wallet text-yellow-500"></i> Payment
            </h3>'''
    old_payment_card_debit = '''<div class="bg-slate-900 rounded-xl p-4 lg:p-5 border border-slate-800 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-red-500/20 to-transparent rounded-bl-full"></div>
            <h3 class="text-white font-bold mb-4 relative z-10">
                <i class="fas fa-hand-holding-usd text-red-500"></i> Payment Status
            </h3>'''
    
    new_payment_card = f'''<div class="bg-slate-900 rounded-2xl shadow-xl border border-slate-800 p-6 relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-full h-1 {'bg-gradient-to-r from-red-400 to-red-600' if is_debit else 'bg-gradient-to-r from-yellow-400 to-yellow-600'}"></div>
            <div class="absolute -right-6 -top-6 w-32 h-32 {'bg-red-500/10 group-hover:bg-red-500/20' if is_debit else 'bg-yellow-500/10 group-hover:bg-yellow-500/20'} rounded-full blur-2xl transition duration-300"></div>
            <h3 class="text-white text-lg font-bold {'mb-4' if is_debit else 'mb-3'} flex items-center gap-3 relative z-10">
                <div class="w-8 h-8 rounded-lg {'bg-red-500/20 text-red-500 border border-red-500/30' if is_debit else 'bg-yellow-500/20 text-yellow-500 border border-yellow-500/30'} flex items-center justify-center shadow-sm">
                    <i class="fas {'fa-hand-holding-usd' if is_debit else 'fa-wallet'} text-sm"></i>
                </div>
                Payment{ " Status" if is_debit else ""}
            </h3>'''
    
    content = content.replace(old_payment_card_debit if is_debit else old_payment_card_cash, new_payment_card)

    # Big Total
    old_total = '<span class="text-2xl lg:text-3xl font-extrabold text-green-400 tracking-tight" x-text="\'Rs. \' + netTotal"></span>'
    new_total = '<span class="text-3xl lg:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-emerald-300 tracking-tight" x-text="\'Rs. \' + netTotal"></span>'
    content = content.replace(old_total, new_total)

    old_total_debit = '<span class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight" x-text="\'Rs. \' + netTotal"></span>'
    new_total_debit = '<span class="text-3xl lg:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-rose-300 tracking-tight" x-text="\'Rs. \' + netTotal"></span>'
    content = content.replace(old_total_debit, new_total_debit)

    # 5. Table wrapper
    old_table_wrap = '<div class="bg-slate-900 rounded-xl border border-slate-800 shadow-lg mb-24 overflow-hidden">'
    new_table_wrap = '''<div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl mb-24 overflow-hidden relative">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-slate-700 to-slate-600"></div>
        <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
             <h3 class="font-bold text-sm text-slate-300 flex items-center gap-2">
                 <i class="fas fa-shopping-cart text-slate-500"></i> Cart Items
             </h3>
        </div>'''
    content = content.replace(old_table_wrap, new_table_wrap)
    
    # Empty State insertion right before <template x-for="(row, index) in rows" :key="index">
    empty_state = f'''
                    <tr x-show="rows.length === 0">
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center opacity-50">
                                <i class="fas fa-shopping-cart text-3xl text-slate-600 mb-3"></i>
                                <span class="text-slate-500 text-sm font-medium">No items added yet</span>
                                <span class="text-slate-600 text-[11px] mt-1">Search or scan barcode below to begin</span>
                            </div>
                        </td>
                    </tr>
                    <template x-for="(row, index) in rows" :key="index">'''
    content = content.replace('<template x-for="(row, index) in rows" :key="index">', empty_state)

    # Thead styling
    old_thead = '<thead class="bg-slate-950 text-slate-300 font-bold uppercase text-xs tracking-wider border-b border-slate-800">'
    new_thead = '<thead class="bg-slate-900 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-800">'
    content = content.replace(old_thead, new_thead)

    with open(filename, 'w') as f:
        f.write(content)

update_file('resources/views/cash-sales/create.blade.php', False)
update_file('resources/views/debit-sales/create.blade.php', True)
print("Updated successfully")
