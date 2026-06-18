/**
 * Ubah objek form (reactive) dari AuctionCreateView menjadi FormData
 * yang siap dikirim ke POST /api/auctions.
 *
 * Field 'antiSnipe' dan 'snipeExtension' SENGAJA tidak disertakan —
 * anti-sniping adalah konfigurasi global (config/auction.php di backend),
 * bukan per-lelang.
 *
 * @param {object} form - form.value dari AuctionCreateView
 * @returns {FormData}
 */
export function buildAuctionFormData(form) {
  const fd = new FormData();

  // ── Informasi barang ──────────────────────────────────────────
  fd.append("title", form.name);
  fd.append("category", form.category);
  fd.append("description", form.description);
  fd.append("condition", form.condition);

  if (form.artist) fd.append("artist", form.artist);
  if (form.year) fd.append("year", form.year);

  // ── Foto ─────────────────────────────────────────────────────
  if (form.mainPhoto) {
    fd.append("main_photo", form.mainPhoto);
  }

  form.extraPhotos.forEach((file) => {
    // Laravel butuh notasi array 'extra_photos[]' agar diterima sebagai array
    fd.append("extra_photos[]", file);
  });

  // ── Pengaturan lelang ────────────────────────────────────────
  fd.append("starting_price", form.startPrice);
  fd.append("bid_increment", form.minIncrement);

  if (form.buyNowPrice) {
    fd.append("buy_now_price", form.buyNowPrice);
  }

  // ── Jadwal ───────────────────────────────────────────────────
  // Dikirim terpisah (snake_case) — backend (StoreAuctionRequest::
  // prepareForValidation) yang akan menggabungkannya jadi starts_at/ends_at
  fd.append("start_date", form.startDate);
  fd.append("start_time", form.startTime);
  fd.append("end_date", form.endDate);
  fd.append("end_time", form.endTime);

  return fd;
}
