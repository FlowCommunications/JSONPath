<?php
namespace Flow\JSONPath\Filters;

use Flow\JSONPath\AccessHelper;
use Flow\Oauth2\Client\Token\Access;
use Flow\JSONPath\ValueObject;

class RecursiveFilter extends AbstractFilter
{
    /**
     * @param $collection
     * @return array
     */
    public function filter($collection)
    {
        $result = [];

        $this->recurse($result, $collection);
        return $result;
    }

    private function recurse(& $result, $data)
    {
		$result[] = new ValueObject($data, $data->path());

        if (AccessHelper::isCollectionType($data)) {
			$keys = AccessHelper::arrayKeys($data);
            foreach (AccessHelper::arrayValues($data) as $key => $value) {

                if (AccessHelper::isCollectionType($value)) {
					$this->recurse($result, new ValueObject($value, static::path($data->path(), $keys[$key])));
                }
            }
        }
    }
}
 
