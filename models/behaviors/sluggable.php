<?php
/**
 * Sluggable Behavior class
 *
 * Creates slugs-key of DB entries on-the-fly
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Lucas Ferreira
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @copyright Copyright 2010-2011, Burn web.studio - http://www.burnweb.com.br/
 * @version 0.7b
 */

class SluggableBehavior extends ModelBehavior
{
	var $options = array();
	
	function setup(&$model, $settings=array())
	{	
	    $_options = array_merge(array(
			'name' => $model->alias,
			'schema' => $model->schema(),		
			'displayField' => $model->displayField,
			'primaryKey' => $model->primaryKey,
			'slugField' => 'slug',
			'replacement' => '-'
		), $settings);
		
		$this->options[$model->alias] = &$_options;
	}
	
	function __slug($description=null, $id=null, $s="-")
	{
		if(function_exists("_slug"))
		{
			return _slug($description, $id, $s);
		}
		else if(class_exists("Util"))
		{
			return Util::slug($description, $id, $s);
		}
		else
		{
			$slugged = Inflector::slug(trim($description), $s) . "{$s}{$id}";
			return function_exists("mb_strtolower") ? mb_strtolower($slugged) : strtolower($slugged);
		}
	}
	
	function beforeFind(&$model, $data=array())
	{
		if(!empty($data['conditions']))
		{
			$slug = null;
			$o = $this->options[$model->alias];
			$conditions = $data['conditions'];
			if(!empty($conditions["{$model->alias}.{$o['slugField']}"]))
			{
				$slug = $conditions["{$model->alias}.{$o['slugField']}"];
				unset($conditions["{$model->alias}.{$o['slugField']}"]);
			}
			if(!empty($conditions["{$o['slugField']}"]))
			{
				$slug = $conditions["{$o['slugField']}"];
				unset($conditions["{$o['slugField']}"]);
			}
			if(!empty($slug))
			{
				$id = end(explode("-", $slug));
				$conditions["{$model->alias}.{$o['primaryKey']}"] = $id;
			}
			$data['conditions'] = $conditions;
		}
		
		return $data;
	}

	function afterFind(&$model, $data=array())
	{
		foreach($data as $i=>$d)
		{
			foreach ($this->options as $ko => $o)
			{
				if(empty($d[$ko][0]))
				{
					if(!empty($d[$ko]) && !empty($d[$ko][$o['displayField']]))
					{
						$ad = $d[$ko];
						$ad[$o['slugField']] = $this->__slug($ad[$o['displayField']], $ad[$o['primaryKey']], $o['replacement']);

						$d[$ko] = $ad;
					}
				}
				else
				{
					foreach ($d[$ko] as $kd => $dd)
					{
						if(!empty($dd) && !empty($dd[$o['displayField']]))
						{
							$dd[$o['slugField']] = $this->__slug($dd[$o['displayField']], $dd[$o['primaryKey']], $o['replacement']);

							$d[$ko][$kd] = $dd;
						}
					}
				}
			}

			$data[$i] = $d;
		}

		return $data;
	}
}
?>