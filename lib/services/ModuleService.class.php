<?php
/**
 * @package modules.gmaps.lib.services
 */
class gmaps_ModuleService extends ModuleBaseService
{
	/**
	 * Singleton
	 * @var gmaps_ModuleService
	 */
	private static $instance = null;

	/**
	 * @return gmaps_ModuleService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}
	
	/**
	 * @param string $address
	 * @return double[] array(<latitude>|null, <longitude>|null)
	 */
	public function getCoordinatesForAddress($address)
	{
		$client = HTTPClientService::getInstance()->getNewHTTPClient();
		$client->setTimeOut(15);
		$jsonResult = $client->get('http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=' . urlencode($address));
		$result = JsonService::getInstance()->decode($jsonResult);
		if ($result['status'] != 'OK')
		{
			if (Framework::isInfoEnabled())
			{
				Framework::info(__METHOD__ . ' Address not found: ' . $address);
				Framework::info(__METHOD__ . ' Result: ' . $jsonResult);
			}
			return array(null, null);
		}
		$location = $result['results'][0]['geometry']['location'];
		return array($location['lat'], $location['lng']);
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocumentModel $model
	 * @param string $latField
	 * @param string $lonField
	 * @param float $refLat
	 * @param float $refLon
	 * @param integer $distance in km
	 * @return integer
	 */
	public function getCountWithinRadius($model, $latField, $lonField, $refLat, $refLon, $distance)
	{
		$tableName = $model->getTableName();
		$sql = $this->getPersistentProvider()->getWithinRadiusQuery($tableName, $latField, $lonField, $refLat, $refLon); 
		$statement = $this->executeSQLSelect($sql);
		$statement->execute();
		return $statement->rowCount();
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocumentModel $model
	 * @param string $latField
	 * @param string $lonField
	 * @param float $refLat
	 * @param float $refLon
	 * @param integer $distance in km
	 * @param integer $offset
	 * @param integer $limit
	 * @return integer[]
	 */
	public function getIdsWithinRadius($model, $latField, $lonField, $refLat, $refLon, $distance, $offset = null, $limit = null)
	{
		$sql = $this->getWithinRadiusQuery($model, $latField, $lonField, $refLat, $refLon, $distance, $offset, $limit); 
		$statement = $this->getPersistentProvider()->executeSQLSelect($sql);
		$statement->execute();
		$rows = $statement->fetchAll();
		$ids = array();
		foreach ($rows as $row)
		{
			$ids[] = $row['id'];
		}
		return $ids;
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocumentModel $model
	 * @param string $latField
	 * @param string $lonField
	 * @param float $refLat
	 * @param float $refLon
	 * @param integer $distance in km
	 * @param integer $offset
	 * @param integer $limit
	 * @return f_persistentdocument_PersistentDocument[]
	 */
	public function getDocsWithinRadius($model, $latField, $lonField, $refLat, $refLon, $distance, $offset = null, $limit = null)
	{
		$ids = $this->getIdsWithinRadius($model, $latField, $lonField, $refLat, $refLon, $distance, $offset, $limit);
		return DocumentHelper::getDocumentArrayFromIdArray($ids);
	}
	
	/**
	 * @param f_persistentdocument_PersistentDocumentModel $model
	 * @param string $latField
	 * @param string $lonField
	 * @param float $refLat
	 * @param float $refLon
	 * @param integer $distance in km
	 * @param integer $offset
	 * @param integer $limit
	 * @return string
	 */
	protected function getWithinRadiusQuery($model, $latField, $lonField, $refLat, $refLon, $distance, $offset = null, $limit = null)
	{
		$tableName = $model->getTableName();
		$modelName = $model->getName();
		$modelNames = $model->getModelChildrenNames($modelName);
		$modelNames[] = $modelName;		
		foreach ($modelNames as $index => $modelName)
		{
			$modelNames[$index] = "'" . $modelName . "'";
		}
		$inModels = '(' . implode(', ', $modelNames) . ')';

		$sql = "SELECT document_id as id, ((ACOS(SIN($refLat * PI() / 180) * SIN($latField * PI() / 180) + COS($refLat * PI() / 180) * COS($latField * PI() / 180) * COS(($refLon - $lonField) * PI() / 180)) * 180 / PI()) * 60 * 1.852) AS `distance` FROM `$tableName` WHERE `document_model` in $inModels";
		$sql .= " HAVING `distance` <= $distance ORDER BY `distance` ASC";
		if ($offset !== null && $limit !== null)
		{
			$sql .= " LIMIT $offset, $limit";
		}
		return $sql;
	}
	
	/**
	 * @param Integer $documentId
	 * @return f_persistentdocument_PersistentTreeNode
	 */
//	public function getParentNodeForPermissions($documentId)
//	{
//		// Define this method to handle permissions on a virtual tree node. Example available in list module.
//	}
}