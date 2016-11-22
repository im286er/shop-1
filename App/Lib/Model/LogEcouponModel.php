<?php
 class LogEcoupon extends RelationModel{
     protected $_link = array(
         'ecoupon'=>array(
             'mapping_type'=>HAS_ONE,
             'class_name'=>'ecoupon',
             'foreign key'=>'ec_id',
             'as_fields'=>'ec_id,ec_code,ec_type'
         )
     )
 }