-- Offers migration: product-level and per-variant temporary pricing with validity windows
-- Products may have a headline offer; storage_options may have an overriding offer price

-- Per-product headline offer (for labeling and default discount when variant-level is not set)
ALTER TABLE products
  ADD COLUMN offer_label VARCHAR(64) NULL AFTER image_url,
  ADD COLUMN offer_price DECIMAL(10,2) NULL AFTER offer_label,
  ADD COLUMN offer_until DATETIME NULL AFTER offer_price,
  ADD COLUMN offer_active TINYINT(1) NOT NULL DEFAULT 0 AFTER offer_until;

-- Per-variant offer price (overrides base price while active)
ALTER TABLE storage_options
  ADD COLUMN offer_price DECIMAL(10,2) NULL AFTER price,
  ADD COLUMN offer_until DATETIME NULL AFTER offer_price,
  ADD COLUMN offer_active TINYINT(1) NOT NULL DEFAULT 0 AFTER offer_until;

-- Helper view (optional): effective price per storage considering offer
-- CREATE OR REPLACE VIEW v_storage_effective_price AS
-- SELECT so.storage_id, so.product_id,
--        CASE WHEN so.offer_active=1 AND so.offer_until IS NOT NULL AND so.offer_until > NOW() AND so.offer_price IS NOT NULL
--             THEN so.offer_price ELSE so.price END AS effective_price
-- FROM storage_options so;

-- Indexes for offer filtering
CREATE INDEX idx_products_offer_until ON products(offer_active, offer_until);
CREATE INDEX idx_storage_offer_until ON storage_options(offer_active, offer_until);

