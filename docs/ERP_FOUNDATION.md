# ERP Foundation

Labelin berkembang sebagai ERP modular dengan label barcode sebagai modul pertama. Setiap modul berbagi `products`, `units`, `warehouses`, `business_partners`, dan ledger stok yang sama.

## Bounded modules

1. **Item Master** — bahan baku, WIP, barang jadi, barang dagang, jasa, satuan, harga, dan metode costing.
2. **Inventory** — saldo per gudang sebagai cache, sedangkan `stock_movements` menjadi sumber audit yang immutable.
3. **Purchasing** — Purchase Order tidak mengubah stok. Goods Receipt yang sudah diposting membuat movement `purchase_receipt`.
4. **Manufacturing / MRP** — BOM berversi, Production Order, material issue (stok keluar), dan production output (stok masuk).
5. **Sales / POS** — transaksi sale, item, pembayaran, pengurangan stok, snapshot HPP, serta gross profit.
6. **Reporting** — laporan stok berasal dari balance + ledger; pembelian dari PO/receipt; penjualan dan margin dari sale + snapshot cost.

## Invariants

- Dokumen berstatus `draft` boleh diedit dan belum menyentuh stok.
- Posting dilakukan di dalam database transaction dan tidak boleh diedit langsung setelah posted.
- Koreksi posted document dilakukan lewat reversal movement, bukan menghapus ledger.
- Semua kuantitas menggunakan decimal 19,4 dan uang menggunakan decimal 19,4.
- `StockService` adalah satu-satunya jalur mutasi saldo stok.
- Produk dan master data memakai soft delete agar histori tetap utuh.
- Nomor dokumen dibuat secara atomic oleh `NumberSequenceService`.

## Suggested implementation order

Inventory → Purchasing/Receiving → BOM/MRP → Production → POS/Sales → reports → accounting integration.
