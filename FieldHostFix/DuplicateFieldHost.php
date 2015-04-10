<?php

#checks fieldhost table for duplicates based on fieldhost identifiers
# SELECT ColEventUID,HostNumber,count(FieldHostUID) as cnt FROM FieldHost GROUP BY CONCAT(ColEventUID,HostNumber) HAVING cnt > 1;
#==========returned none!

#SELECT HostParentID, HostTaxName, count(HostMNLUID) as cnt FROM Flora_MNL GROUP BY CONCAT(HostTaxName,HostParentID) HAVING cnt > 1;

#======returned none!

?>