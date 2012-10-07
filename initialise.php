<?php
// this file initilises the application
// that means it will create database tables which doesn't exist
// add new days to the calendar
// and other stuff

Plant::createIfNecessary();
Session::createIfNecessary();
Day::createIfNecessary();
MeasurementRaw::createIfNecessary();
Measurement::createIfNecessary();
QuarterHoursOfTheDay::createIfNecessary();

// create days until 01.01.2015
Day::createDays(strtotime("01.01.2015"));
?>
