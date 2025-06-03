# WhatsApp tracking fields
 
ALTER TABLE `reservations` 
ADD COLUMN `whatsapp_sent` TINYINT(1) DEFAULT 0 AFTER `active_auto_notify`,
ADD COLUMN `whatsapp_sent_at` TIMESTAMP NULL DEFAULT NULL AFTER `whatsapp_sent`,
ADD COLUMN `whatsapp_message_id` VARCHAR(255) NULL DEFAULT NULL AFTER `whatsapp_sent_at`;

