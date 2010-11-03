<?php
/**
 * Sluggable Behavior class
 *
 * Creates slugs-key of DB entries
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Lucas Ferreira
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @copyright Copyright 2010, Burn web.studio - http://www.burnweb.com.br/
 * @version 0.3b
 */

class SluggableBehavior extends ModelBehavior
{
	var $options = array();
	
	function setup(&$model, $settings=array())
	{	
	    $_options = array_merge(array('displayField' => $model->displayField, 'primaryKey' => $model->primaryKey, 'slugField' => 'slug'), $settings);
		$this->options[$model->name] = &$_options;
	}
	
	function __slug($description=null, $id=null)
	{
		if(function_exists("_slug"))
		{
			return _slug($description, $id);
		}
		else if(class_exists("Util"))
		{
			return Util::slug($description, $id);
		}
		else
		{
			$slugged = Inflector::slug(trim($description), "-") . "-{$id}";
			return function_exists("mb_strtolower") ? mb_strtolower($slugged) : strtolower($slugged);
		}
	}
	
	function beforeFind(&$model, $data=array())
	{
		if(!empty($data['conditions']))
		{
			$slug = null;
			$o = $this->options[$model->name];
			$conditions = $data['conditions'];
			if(!empty($conditions["{$model->name}.{$o['slugField']}"]))
			{
				$slug = $conditions["{$model->name}.{$o['slugField']}"];
				unset($conditions["{$model->name}.{$o['slugField']}"]);
			}
			if(!empty($conditions["{$o['slugField']}"]))
			{
				$slug = $conditions["{$o['slugField']}"];
				unset($conditions["{$o['slugField']}"]);
			}
			if(!empty($slug))
			{
				$id = end(explode("-", $slug));
				$conditions["{$model->name}.{$o['primaryKey']}"] = $id;
			}
			$data['conditions'] = $conditions;
		}
		
		return $data;
	}

	function afterFind(&$model, $data=array())
	{
		$o = $this->options[$model->name];
		
		foreach($data as $i=>$d)
		{
			if(!empty($d[$model->name]))
			{
				$ad = $d[$model->name];
				$ad[$o['slugField']] = $this->__slug($ad[$o['displayField']], $ad[$o['primaryKey']]);

				$d[$model->name] = $ad;
			}

			$data[$i] = $d;
		}

		return $data;
	}
}
?>