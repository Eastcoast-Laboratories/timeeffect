-- Migration: Add customer_address column to customer table
-- Date: 2025-08-27
-- Purpose: Fix missing customer_address column for invoice system

ALTER TABLE `<%db_prefix%>customer` 
ADD COLUMN `customer_address` TEXT NULL AFTER `customer_desc`;

-- Update existing customers with empty address if needed
UPDATE `<%db_prefix%>customer` 
SET `customer_address` = '' 
WHERE `customer_address` IS NULL;
