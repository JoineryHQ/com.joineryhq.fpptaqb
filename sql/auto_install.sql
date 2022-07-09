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

DROP TABLE IF EXISTS `civicrm_fpptaquickbooks_trxn_payment`;
DROP TABLE IF EXISTS `civicrm_fpptaquickbooks_log`;
DROP TABLE IF EXISTS `civicrm_fpptaquickbooks_financialtype_item`;
DROP TABLE IF EXISTS `civicrm_fpptaquickbooks_contribution_invoice`;
DROP TABLE IF EXISTS `civicrm_fpptaquickbooks_contact_customer`;

SET FOREIGN_KEY_CHECKS=1;
-- /*******************************************************
-- *
-- * Create new tables
-- *
-- *******************************************************/

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
  `quickbooks_id` varchar(255) COMMENT 'Quickbooks customer ID',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `UI_fpptaquickbooks_contact_customer_contact_id`(contact_id),
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
  UNIQUE INDEX `UI_fpptaquickbooks_contribution_invoice_contribution_id`(contribution_id),
  CONSTRAINT FK_civicrm_fpptaquickbooks_contribution_invoice_contribution_id FOREIGN KEY (`contribution_id`) REFERENCES `civicrm_contribution`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_fpptaquickbooks_financialtype_item
-- *
-- * Link civicrm financial type to quickbooks item/product
-- *
-- *******************************************************/
CREATE TABLE `civicrm_fpptaquickbooks_financialtype_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FpptaquickbooksFinancialTypeItem ID',
  `financial_type_id` int unsigned COMMENT 'FK to Financial Type',
  `quickbooks_id` int unsigned COMMENT 'Quickbooks item ID',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `UI_civicrm_fpptaquickbooks_financialtype_item_financial_type_id`(financial_type_id),
  CONSTRAINT FK_civicrm_fpptaquickbooks_financialtype_item_financial_type_id FOREIGN KEY (`financial_type_id`) REFERENCES `civicrm_financial_type`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_fpptaquickbooks_log
-- *
-- * Log relevant api calls for fpptaqb
-- *
-- *******************************************************/
CREATE TABLE `civicrm_fpptaquickbooks_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FpptaquickbooksLog ID',
  `created` datetime COMMENT 'When was the log entry created.',
  `contact_id` int unsigned COMMENT 'Contact who created this log entry; FK to civicrm_contact',
  `unique_request_id` varchar(64) COMMENT 'Unique identifier for a single php invocation.',
  `entity_id_param` varchar(64) COMMENT 'Name of api parameter identifying the relevant entity.',
  `entity_id` int unsigned COMMENT 'Foreign key to the referenced item.',
  `api_entity` varchar(64) COMMENT 'API entity for the api call which triggered this log entry',
  `api_action` varchar(64) COMMENT 'API action for the api call which triggered this log entry',
  `api_params` varchar(2550) COMMENT 'API parameters for the api call which triggered this log entry',
  `api_output` text COMMENT 'API parameters for the api call which triggered this log entry',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_fpptaquickbooks_log_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_fpptaquickbooks_trxn_payment
-- *
-- * Link civicrm financial transaction payments to quickbooks payments
-- *
-- *******************************************************/
CREATE TABLE `civicrm_fpptaquickbooks_trxn_payment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FpptaquickbooksTrxnPayment ID',
  `financial_trxn_id` int unsigned COMMENT 'FK to civicrm_financial_trxn',
  `quickbooks_id` int unsigned COMMENT 'Quickbooks payment ID',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `UI_fpptaquickbooks_trxn_payment_financial_trxn_id`(financial_trxn_id),
  CONSTRAINT FK_civicrm_fpptaquickbooks_trxn_payment_financial_trxn_id FOREIGN KEY (`financial_trxn_id`) REFERENCES `civicrm_financial_trxn`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;
