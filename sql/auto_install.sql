-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC. All rights reserved.                        |
-- |                                                                    |
-- | This work is published under the GNU AGPLv3 license with some      |
-- | permitted exceptions and without any warranty. For full license    |
-- | and copyright information, see https://civicrm.org/licensing       |
-- +--------------------------------------------------------------------+
--
-- Generated from schema.tpl
-- DO NOT EDIT.  Generated by CRM_Core_CodeGen
--
-- /*******************************************************
-- *
-- * Clean up the existing tables - this section generated from drop.tpl
-- *
-- *******************************************************/

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civicrm_fpptaquickbooks_contribution_invoice`;
DROP TABLE IF EXISTS `civicrm_fpptaquickbooks_contact_customer`;
DROP TABLE IF EXISTS `civicrm_fpptaquickbooks_account_item`;

SET FOREIGN_KEY_CHECKS=1;
-- /*******************************************************
-- *
-- * Create new tables
-- *
-- *******************************************************/

-- /*******************************************************
-- *
-- * civicrm_fpptaquickbooks_account_item
-- *
-- * Link civicrm financial account to quickbooks item/product
-- *
-- *******************************************************/
CREATE TABLE `civicrm_fpptaquickbooks_account_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FpptaquickbooksAccountItem ID',
  `financial_account_id` int unsigned COMMENT 'FK to Financial Account',
  `quickbooks_id` int unsigned COMMENT 'Quickbooks invoice ID',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_fpptaquickbooks_account_item_financial_account_id FOREIGN KEY (`financial_account_id`) REFERENCES `civicrm_financial_account`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_fpptaquickbooks_contact_customer
-- *
-- * Link civicrm contact to quickbooks customer
-- *
-- *******************************************************/
CREATE TABLE `civicrm_fpptaquickbooks_contact_customer` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FpptaquickbooksContactCustomer ID',
  `contact_id` int unsigned COMMENT 'FK to Contact',
  `quickbooks_id` int unsigned COMMENT 'Quickbooks customer ID',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_fpptaquickbooks_contact_customer_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_fpptaquickbooks_contribution_invoice
-- *
-- * Link civicrm contributions to quickbooks invoices
-- *
-- *******************************************************/
CREATE TABLE `civicrm_fpptaquickbooks_contribution_invoice` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FpptaquickbooksContributionInvoice ID',
  `contribution_id` int unsigned COMMENT 'FK to Contribution',
  `quickbooks_id` int unsigned COMMENT 'Quickbooks invoice ID',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_fpptaquickbooks_contribution_invoice_contribution_id FOREIGN KEY (`contribution_id`) REFERENCES `civicrm_contribution`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;
