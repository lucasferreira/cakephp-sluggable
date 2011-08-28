# Sluggable Behavior for CakePHP

Make slugs fields on-the-fly

## Usage:

Load the Sluggable behavior in your model:

	var $actsAs = array('Sluggable');
	
When you find some data, the new slug virtual field will be in our results:

	$entry = $this->YourModel->find('all');
	
	pr($entry);
	
	//array(0 => array('YourModel' => array('slug' => 'your-entry-name-1')))
	
## Configure:

If you need configure the slug acts you can do with this options:

	var $actsAs = array('Sluggable' => array(
		'displayField' => 'yourDisplayFieldName',
		'primaryKey' => 'yourPrimaryKeyFieldName',
		'slugField' => 'theNameOfYourSlugVirtualField',
		'replacement' => '_' //the char to implode the words in entry name...
	));
	
## Find data with slugs:

You can use this behavior to find data with slugs names:

	$entry = $this->YourModel->find('all', array(
		'conditions' => array(
			'YourModel.slug' => 'your-entry-name-1'
		)
	));
	
	pr($entry);
	
	
@lucasferreira