<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Data;


use Fewd\Core\ATest;
use Fewd\Core\TCore;
use Fewd\Tracer\TTracer;


class TDataTest extends ATest
{
	//------------------------------------------------------------------------------------------------------------------
	// Tests database management
	//------------------------------------------------------------------------------------------------------------------
	protected function RunDatabase(ADatabase $database)
	{
		// Query wildcards
		$wildcards = $database->QueryWildcards('SELECT :test AS test FROM mytable WHERE id = :id');
		$this->CheckArrayKeys($wildcards, array('test', 'id'));

		// Query types
		$this->Check($database->QueryType('SELECT * FROM test'), TData::QUERY_SELECT);
		$this->Check($database->QueryType(' INSERT INTO test' ), TData::QUERY_INSERT);
		$this->Check($database->QueryType('update test'       ), TData::QUERY_UPDATE);
		$this->Check($database->QueryType('DELETE test'       ), TData::QUERY_DELETE);
		$this->Check($database->QueryType('TRUNCATE test'     ), TData::QUERY_TRUNCATE);
		$this->Check($database->QueryType('CREATE TABLE test' ), TData::QUERY_CREATE);
		$this->Check($database->QueryType('CREATE INDEX test' ), '');
		$this->Check($database->QueryType('DROP TABLE test'   ), TData::QUERY_DROP);
		$this->Check($database->QueryType('DROP INDEX test'   ), '');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Tests datatable management
	//------------------------------------------------------------------------------------------------------------------
	protected function RunDatatable(ADatabase $database)
	{
		$data = $database->Data();

		// Datatable creation with specific instructions + fulltexts
		$datatable = $data->MakeDatatable($database, 'test');
		$datatable->AddKey(     'id'  , TData::DATATYPE_CODE    );
		$datatable->AddField(   'name', TData::DATATYPE_TEXT    );
		$datatable->AddField(   'when', TData::DATATYPE_DATETIME);
		$datatable->AddFulltext('name');

		$this->Check($datatable->CreateQuery(),
			"CREATE TABLE IF NOT EXISTS `test` (\n" .
			"\t`id` VARCHAR(50) NOT NULL,\n" .
			"\t`name` VARCHAR(255) NOT NULL,\n" .
			"\t`when` CHAR(14) NOT NULL,\n" .
			"FULLTEXT `test__fulltext__name` (`name`),\n" .
			"PRIMARY KEY (\n" .
			"\t`id`\n" .
			"))\n" .
			"ENGINE=MyISAM DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci");

		$this->Check($datatable->TruncateQuery(), 'TRUNCATE TABLE `test`');

		$this->Check($datatable->DropQuery(), 'DROP TABLE IF EXISTS `test`');

		// Datatable creation in one instruction
		$datatable = $data->MakeDatatable($database, 'test', array(
			'id*' => TData::DATATYPE_CODE,
			'name' => TData::DATATYPE_TEXT,
			'when' => TData::DATATYPE_DATETIME));

		$this->Check($datatable->CreateQuery(),
			"CREATE TABLE IF NOT EXISTS `test` (\n" .
			"\t`id` VARCHAR(50) NOT NULL,\n" .
			"\t`name` VARCHAR(255) NOT NULL,\n" .
			"\t`when` CHAR(14) NOT NULL,\n" .
			"PRIMARY KEY (\n" .
			"\t`id`\n" .
			"))\n" .
			"ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci");

		// Datatable truncation
		$this->Check($datatable->TruncateQuery(), 'TRUNCATE TABLE `test`');

		// Datatable drop
		$this->Check($datatable->DropQuery(), 'DROP TABLE IF EXISTS `test`');

		// Datatable sort
		$datatable->SortOn();

		$this->Check($datatable->CreateQuery(),
			"CREATE TABLE IF NOT EXISTS `test` (\n" .
			"\t`id` VARCHAR(50) NOT NULL,\n" .
			"\t`name` VARCHAR(255) NOT NULL,\n" .
			"\t`when` CHAR(14) NOT NULL,\n" .
			"\t`sort` CHAR(22) NOT NULL,\n" .
			"PRIMARY KEY (\n" .
			"\t`id`\n" .
			"))\n" .
			"ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci");

		$datatable->SortOff();

		// Datatable management
		$datatable->ManagementOn();

		$this->Check($datatable->CreateQuery(),
			"CREATE TABLE IF NOT EXISTS `test` (\n" .
			"\t`id` VARCHAR(50) NOT NULL,\n" .
			"\t`name` VARCHAR(255) NOT NULL,\n" .
			"\t`when` CHAR(14) NOT NULL,\n" .
			"\t`created_by` VARCHAR(50) NOT NULL,\n" .
			"\t`created_when` CHAR(14) NOT NULL,\n" .
			"\t`updated_by` VARCHAR(50) NOT NULL,\n" .
			"\t`updated_when` CHAR(14) NOT NULL,\n" .
			"PRIMARY KEY (\n" .
			"\t`id`\n" .
			"))\n" .
			"ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci");
	}


	//------------------------------------------------------------------------------------------------------------------
	// Tests SELECT
	//------------------------------------------------------------------------------------------------------------------
	public function RunSelect(ADatabase $database)
	{
		$data = $database->Data();

		$datatable = $data->MakeDatatable($database, 'test', array(
			'id*'     => TData::DATATYPE_CODE,
			'name'    => TData::DATATYPE_TEXT,
			'type'    => TData::DATATYPE_TEXT,
			'counter' => TData::DATATYPE_NUMBER,
			'when'    => TData::DATATYPE_DATETIME));

		// Simple SELECT without fields
		$select = $data->MakeSelect($datatable, 'y');

		$bindings = array();
		$this->Check($select->Query($bindings),
			"SELECT\n" .
			"\ty.*\n" .
			"FROM\n" .
			"\t`test` y");

		// Simple SELECT DISTINCT
		$select->DistinctOn();

		$this->Check($select->Query($bindings),
			"SELECT DISTINCT\n" .
			"\ty.*\n" .
			"FROM\n" .
			"\t`test` y");

		$select->DistinctOff();

		// SELECT with multiple fields and datatable alias
		$select->AddField('a', '');
		$select->AddField('b', 'b');
		$select->AddField('c&', 'my value');
		$select->AddField('d@', '(1 + 2)');
		$select->AddField('e:', '42');
		$select->AddField('f', 'name');

		$select->SetAlias('z');

		$this->Check($select->Query($bindings),
			"SELECT\n" .
			"\tz.`a` AS `a`,\n" .
			"\tz.`b` AS `b`,\n" .
			"\t'my value' AS `c`,\n" .
			"\t(1 + 2) AS `d`,\n" .
			"\t:field_1 AS `e`,\n" .
			"\tz.`name` AS `f`\n" .
			"FROM\n" .
			"\t`test` z");

		// SELECT with multiple fields and datatable alias
		$select->ClearFields();
		$select->AddCondition('name&~', 'f%');
		$select->AddCondition('when&', array('test', 33));
		$select->AddCondition('when' , array('test', 33));

		$this->Check($select->Query($bindings),
			"SELECT\n" .
			"\tz.*\n" .
			"FROM\n" .
			"\t`test` z\n" .
			"WHERE z.`name` LIKE 'f%'\n" .
			"AND   (z.`when` = 'test' OR z.`when` = 33)\n" .
			"AND   (z.`when` = :condition_1 OR z.`when` = :condition_2)");

		// SELECT with GROUP BY / HAVING and bindings check
		$select->ClearFields();
		$select->AddField('name', TData::AGGREGATION_MAXIMUM);
		$select->AddGroup('type');
		$select->AddHaving('name>', 'AA');
		$select->AddHaving('name~', '%C%');

		$this->Check($select->Query($bindings),
			"SELECT\n" .
			"\tMAX(z.`name`) AS `name`\n" .
			"FROM\n" .
			"\t`test` z\n" .
			"WHERE z.`name` LIKE 'f%'\n" .
			"AND   (z.`when` = 'test' OR z.`when` = 33)\n" .
			"AND   (z.`when` = :condition_1 OR z.`when` = :condition_2)\n" .
			"GROUP BY\n" .
			"\tz.`type`\n" .
			"HAVING z.`name` > :condition_3\n" .
			"AND    z.`name` LIKE :condition_4");

		$this->CheckArray($bindings, array(
			'condition_1' => 'test',
			'condition_2' => 33,
			'condition_3' => 'AA',
			'condition_4' => '%C%'));

		// SELECT with JOIN
		$typesDatatable = $data->MakeDatatable($database, 'types', array(
			'type*' => TData::DATATYPE_CODE,
			'name'  => TData::DATATYPE_TEXT));

		$join = $select->DeclareJoin($typesDatatable, array('type' => 'type'));
		$join->AddField('label');
		$join->SetAlias('ajoin');

		$this->Check($select->Query($bindings),
			"SELECT\n" .
			"\tMAX(z.`name`) AS `name`,\n" .
			"\tajoin.`label` AS `label`\n" .
			"FROM\n" .
			"\t`test` z\n" .
			"\n" .
			"\tINNER JOIN `types` ajoin\n" .
			"\tON  ajoin.`type` = z.`type`\n" .
			"WHERE z.`name` LIKE 'f%'\n" .
			"AND   (z.`when` = 'test' OR z.`when` = 33)\n" .
			"AND   (z.`when` = :condition_1 OR z.`when` = :condition_2)\n" .
			"GROUP BY\n" .
			"\tz.`type`\n" .
			"HAVING z.`name` > :condition_3\n" .
			"AND    z.`name` LIKE :condition_4");
	}


	//------------------------------------------------------------------------------------------------------------------
	// Tests SELECT with JOINS
	//------------------------------------------------------------------------------------------------------------------
	protected function RunSelectWithJoins(ADatabase $database)
	{
		// Datatables
		$data = $database->Data();

		$datatableElements = $data->MakeDatatable($database, 'elements', array(
			'element*' => TData::DATATYPE_CODE,
			'name'     => TData::DATATYPE_TEXT,
			'family1'  => TData::DATATYPE_CODE,
			'family2'  => TData::DATATYPE_CODE));

		$datatableFamilies = $data->MakeDatatable($database, 'families', array(
			'family*' => TData::DATATYPE_CODE,
			'name'    => TData::DATATYPE_TEXT));

		// Select
		$select = $data->MakeSelect($datatableElements, 'T');

		$select->AddField('element');
		$select->AddField('name');
		$select->AddField('family');

		// Join 1
		$select->DeclareJoin(
			$datatableFamilies,               // Joined datatable
			array('family' => 'family1'),     // Links
			array('name1'  => 'name'),        // Fields
			array('name>'  => 'A'),           // Conditions
			TData::JOINTYPE_INNER,            // Join type
			'J1');                            // Alias

		// Join 2
		$join2 = $select->DeclareJoin(
			$datatableFamilies,
			array('family' => 'family2'));

		$join2->AddField('name2', 'name');
		$join2->AddCondition('name!?', '');
		$join2->SetJointype(TData::JOINTYPE_LEFT);
		$join2->SetAlias('J2');

		// Test
		$this->Check($select->DirectQuery(),
		"SELECT\n" .
		"\tT.`element` AS `element`,\n" .
		"\tT.`name` AS `name`,\n" .
		"\tT.`family` AS `family`,\n" .
		"\tJ1.`name` AS `name1`,\n" .
		"\tJ2.`name` AS `name2`\n" .
		"FROM\n" .
		"\t`elements` T\n" .
		"\n" .
		"\tINNER JOIN `families` J1\n" .
		"\tON  J1.`family` = T.`family1`\n" .
		"\n" .
		"\tLEFT JOIN `families` J2\n" .
		"\tON  J2.`family` = T.`family2`\n" .
		"WHERE J1.`name` > 'A'\n" .
		"AND   J2.`name` IS NOT NULL");
	}

	//------------------------------------------------------------------------------------------------------------------
	// Tests INSERT
	//------------------------------------------------------------------------------------------------------------------
	protected function RunInsert(ADatabase $database)
	{
		$data = $database->Data();

		$datatable = $data->MakeDatatable($database, 'test', array(
			'id*'     => TData::DATATYPE_CODE,
			'name'    => TData::DATATYPE_TEXT,
			'type'    => TData::DATATYPE_TEXT,
			'counter' => TData::DATATYPE_NUMBER,
			'when'    => TData::DATATYPE_DATETIME));

		// INSERT query
		$insert = $data->MakeInsert($datatable);

		$insert->AddRecord(array(
			'id'      => '1234',
			'name'    => 'my name',
			'type'    => 'my \'type\'',
			'counter' => 3,
			'when'    => '20200101000000'));

		$bindings = array();

		$this->Check($insert->Query($bindings),
			"INSERT INTO `test`\n" .
			"(\n" .
			"\t`id`,\n" .
			"\t`name`,\n" .
			"\t`type`,\n" .
			"\t`counter`,\n" .
			"\t`when`\n" .
			") VALUES\n" .
			"(\n" .
			"\t:value_1,\n" .
			"\t:value_2,\n" .
			"\t:value_3,\n" .
			"\t:value_4,\n" .
			"\t:value_5\n" .
			")");

		// INSERT on a sorted datatable
		$microtime = $database->Microtime();

		$datatable->SortOn();

		$this->Check($insert->Query($bindings),
			"INSERT INTO `test`\n" .
			"(\n" .
			"\t`id`,\n" .
			"\t`name`,\n" .
			"\t`type`,\n" .
			"\t`counter`,\n" .
			"\t`when`,\n" .
			"\t`sort`\n" .
			") VALUES\n" .
			"(\n" .
			"\t:value_1,\n" .
			"\t:value_2,\n" .
			"\t:value_3,\n" .
			"\t:value_4,\n" .
			"\t:value_5,\n" .
			"\t:value_6\n" .
			")");

		$this->CheckArrayValue($bindings, 'value_6', $microtime);

		$datatable->SortOff();

		// INSERT on a managed datatable
		$by   = $database->By();
		$when = $database->When();

		$datatable->ManagementOn();

		$this->Check($insert->Query($bindings),
			"INSERT INTO `test`\n" .
			"(\n" .
			"\t`id`,\n" .
			"\t`name`,\n" .
			"\t`type`,\n" .
			"\t`counter`,\n" .
			"\t`when`,\n" .
			"\t`created_by`,\n" .
			"\t`created_when`,\n" .
			"\t`updated_by`,\n" .
			"\t`updated_when`\n" .
			") VALUES\n" .
			"(\n" .
			"\t:value_1,\n" .
			"\t:value_2,\n" .
			"\t:value_3,\n" .
			"\t:value_4,\n" .
			"\t:value_5,\n" .
			"\t:value_6,\n" .
			"\t:value_7,\n" .
			"\t:value_8,\n" .
			"\t:value_9\n" .
			")");

		$this->CheckArrayValue($bindings, 'value_6', $by);
		$this->CheckArrayValue($bindings, 'value_7', $when);
		$this->CheckArrayValue($bindings, 'value_8', $by);
		$this->CheckArrayValue($bindings, 'value_9', $when);

		$datatable->ManagementOff();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Tests UPDATE
	//------------------------------------------------------------------------------------------------------------------
	protected function RunUpdate(ADatabase $database)
	{
		$data = $database->Data();

		$datatable = $data->MakeDatatable($database, 'test', array(
			'id*'     => TData::DATATYPE_CODE,
			'name'    => TData::DATATYPE_TEXT,
			'type'    => TData::DATATYPE_TEXT,
			'counter' => TData::DATATYPE_NUMBER,
			'when'    => TData::DATATYPE_DATETIME));

		// UPDATE for some fields
		$update = $data->MakeUpdate($datatable,
			array(
				'name'        => 'my new name',
				'type'        => 'my new \'type\'',
				'counter'     => 4,
				'wrong_field' => 'wrong_value'),
			array(
				'id'          => '1234'));

		$bindings = array();
		$this->Check($update->Query($bindings),
			"UPDATE `test` SET\n" .
			"\t`name` = :value_1,\n" .
			"\t`type` = :value_2,\n" .
			"\t`counter` = :value_3\n" .
			"WHERE `id` = :condition_4");

		// UPDATE of a managed datatable
		$datatable->ManagementOn();
		$by   = $database->By();
		$when = $database->When();

		$update = $data->MakeUpdate($datatable,
			array(
				'name'         => 'my new name',
				'created_by'   => 'anyone',
				'updated_when' => 'anytime'));

		$bindings = array();
		$this->Check($update->Query($bindings),
			"UPDATE `test` SET\n" .
			"\t`name` = :value_1,\n" .
			"\t`updated_by` = :value_2,\n" .
			"\t`updated_when` = :value_3");

		$this->CheckArray($bindings, array(
			'value_1' => 'my new name',
			'value_2' => $by,
			'value_3' => $when));

		$datatable->ManagementOff();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Tests DELETE
	//------------------------------------------------------------------------------------------------------------------
	protected function RunDelete(ADatabase $database)
	{
		$data = $database->Data();

		$datatable = $data->MakeDatatable($database, 'test', array(
			'id*'     => TData::DATATYPE_CODE,
			'name'    => TData::DATATYPE_TEXT,
			'type'    => TData::DATATYPE_TEXT,
			'counter' => TData::DATATYPE_NUMBER,
			'when'    => TData::DATATYPE_DATETIME));

		// DELETE with multiple fields and datatable alias
		$delete = $data->MakeDelete($datatable);
		$delete->SetAlias('z');
		$delete->AddCondition('name&~', 'f%');
		$delete->AddCondition('when', array('test', 33));

		$bindings = array();
		$this->Check($delete->Query($bindings),
			"DELETE FROM `test` z\n" .
			"WHERE z.`name` LIKE 'f%'\n" .
			"AND   (z.`when` = :condition_1 OR z.`when` = :condition_2)");
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs the test
	//------------------------------------------------------------------------------------------------------------------
	public function Run()
	{
		$core = new TCore();
		$core->Init();

		$tracer = new TTracer($core, 'log');
		$tracer->Init();

		$data = new TData($core, $tracer);
		$data->Init();

		$database = $data->MakeDatabaseMysql('datatest', 'myhost', 'myport', 'myuser', 'mypass');

		// Database tests
		$this->RunDatabase($database);

		// Datatable tests
		$this->RunDatatable($database);

		// Sql tests
		$this->RunSelect(         $database);
		$this->RunSelectWithJoins($database);
		$this->RunInsert(         $database);
		$this->RunUpdate(         $database);
		$this->RunDelete(         $database);
	}
}
