# FEWD - Just a FEW Development


## What is it ?

FEWD is yet another lightweight PHP framework.

Quick to learn, quick to use, quick to run.

It can be used as a foundation for a new project, or as a complement to any other framework, due to its modular approach.


## Usage

Fewd can be uploaded anywhere in your project (let's say, under `/Lib`).

	<?php

		include 'Lib/Fewd.php';

And that's it.


## FEWD is composed by modules

Modules basically are *folders* under your FEWD directory.
Custom modules can also be created and put wherever you want.

If `Xyz` is the module name, then there will be a file called `TXyz.php`, it describes the class `TXyz` that is intended to be instantiated as a singleton. This singleton will deliver all general methods and properties of the module.

A module may contain php class files, image files, css files and other resources.

Major modules are :

| Name       | Objective                                                                    |
|------------|------------------------------------------------------------------------------|
| CORE       | Contains everything needed to build a new module and some generic tools      |
| TIMER      | Tools for performance benchmark                                              |
| TRACER     | Tools for debugging                                                          |
| ROUTER     | Associates an Url to a route                                                 |
| HTML       | Html document generation                                                     |
| BOOTSTRAP  | A set of Html starting components + component resize queries                 |
| TRANSLATOR | Internationalization                                                         |
| APP        | An all-in-one app generator                                                  |


### Unit testing

Under the module, there is also a file called `TXyzTest.php`. It is the unit test script.

To run a unit test :

	<? php

	use Fewd\Core\TCore;

	$test = new TCoreTest();
	$test->Run();

	// Any failure will be prompted directly on screen.
	// But if you want to get the detail from all detected failures :
	var_dump($test->Failures());


### Instantiation

To instantiate the `Xyz` module singleton :

	<?php

	use <path>\Xyz\TXyz;

	$xyz = new TXyz(<parameters>);
	$xyz->Init();

And that's it.

Please note : Never forget the `Init()` call just after instantiation !
