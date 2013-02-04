<?php
/* ----------------------------------------------------------------------
 * entitySplitterRefinery.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2013 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * ----------------------------------------------------------------------
 */
 	require_once(__CA_LIB_DIR__.'/ca/Import/BaseRefinery.php');
 	require_once(__CA_LIB_DIR__.'/ca/Utils/DataMigrationUtils.php');
 
	class entitySplitterRefinery extends BaseRefinery {
		# -------------------------------------------------------
		
		# -------------------------------------------------------
		public function __construct() {
			$this->ops_name = 'entitySplitter';
			$this->ops_title = _t('Entity splitter');
			$this->ops_description = _t('Splits entities');
			
			parent::__construct();
		}
		# -------------------------------------------------------
		/**
		 * Override checkStatus() to return true
		 */
		public function checkStatus() {
			return array(
				'description' => $this->getDescription(),
				'errors' => array(),
				'warnings' => array(),
				'available' => true,
			);
		}
		# -------------------------------------------------------
		/**
		 *
		 */
		public function refine(&$pa_destination_data, $pa_group, $pa_item, $pa_source_data, $pa_options=null) {
			$va_group_dest = explode(".", $pa_group['destination']);
			$vs_terminal = array_pop($va_group_dest);
			$pm_value = $pa_source_data[$pa_item['source']];
			
			if ($vs_delimiter = $pa_item['settings']['entitySplitter_delimiter']) {
				$va_entities = explode($vs_delimiter, $pm_value);
			} else {
				$va_entities = array($pm_value);
			}
			
			//print_R($pa_item);
			
			$va_vals = array();
			foreach($va_entities as $vn_i => $vs_entity) {
				if (!$vs_entity = trim($vs_entity)) { continue; }
				
				$va_split_name = DataMigrationUtils::splitEntityName($vs_entity);
		
				if(isset($va_split_name[$vs_terminal])) {
					return $va_split_name[$vs_terminal];
				}
			
				if (in_array($vs_terminal, array('preferred_labels', 'nonpreferred_labels'))) {
					return $va_split_name;	
				}
			
				// Set label
				$va_val = array('preferred_labels' => $va_split_name);
			
				// Set relationship type
				if ($vs_rel_type_opt = $pa_item['settings']['entitySplitter_relationshipType']) {
					$va_val['_relationship_type'] = BaseRefinery::parsePlaceholder($vs_rel_type_opt, $pa_source_data, $pa_item, $vs_delimiter, $vn_i);
				}
			
				// Set entity_type
				;
				if ($vs_type_opt = $pa_item['settings']['entitySplitter_entityType']) {
					$va_val['_type'] = BaseRefinery::parsePlaceholder($vs_type_opt, $pa_source_data, $pa_item);
				}
			
				// Set attributes
				if (is_array($va_attrs = $pa_item['settings']['entitySplitter_attributes'])) {
					foreach($pa_item['settings']['entitySplitter_attributes'] as $vs_element_code => $va_attrs) {
						if(is_array($va_attrs)) {
							foreach($va_attrs as $vs_k => $vs_v) {
								$pa_item['settings']['entitySplitter_attributes'][$vs_element_code][$vs_k] = BaseRefinery::parsePlaceholder($vs_v, $pa_source_data, $pa_item);
							}
						}
					}
					$va_val = array_merge($va_val, $va_attrs);
				}
				
				$va_vals[] = $va_val;
			}
			
			return $va_vals;
		}
		# -------------------------------------------------------
	}
	
	 BaseRefinery::$s_refinery_settings['entitySplitter'] = array(		
			'entitySplitter_delimiter' => array(
				'formatType' => FT_TEXT,
				'displayType' => DT_SELECT,
				'width' => 10, 'height' => 1,
				'takesLocale' => false,
				'default' => '',
				'label' => _t('Delimiter'),
				'description' => _t('Delimiter')
			),
			'entitySplitter_relationshipType' => array(
				'formatType' => FT_TEXT,
				'displayType' => DT_SELECT,
				'width' => 10, 'height' => 1,
				'takesLocale' => false,
				'default' => '',
				'label' => _t('Relationship type'),
				'description' => _t('Relationship type')
			),
			'entitySplitter_entityType' => array(
				'formatType' => FT_TEXT,
				'displayType' => DT_SELECT,
				'width' => 10, 'height' => 1,
				'takesLocale' => false,
				'default' => '',
				'label' => _t('Entity type'),
				'description' => _t('Entity type')
			),
			'entitySplitter_attributes' => array(
				'formatType' => FT_TEXT,
				'displayType' => DT_SELECT,
				'width' => 10, 'height' => 1,
				'takesLocale' => false,
				'default' => '',
				'label' => _t('Attributes'),
				'description' => _t('Attributes')
			)
		);
?>