Scripts for updating the fieldhost table to a b-tree and matching the flora table.

Notes:
select distinct FieldHost.HostG, F3.HostTaxName, FieldHost.HostSp,F1.HostTaxName as HostSpName, FieldHost.HostAuthor, F2.HostTaxName  as HostAuthor from FieldHost left join Flora_MNL F1 on FieldHost.HostSp=F1.HostMNLUID left join Flora_MNL F2 on FieldHost.HostAuthor=F2.HostMNLUID left join Flora_MNL F3 on FieldHost.HostG=F3.HostMNLUIDorder by HostSpName;


pbi_localityOLD fieldhost table

+--------------+------------------+------+-----+---------+----------------+
| Field        | Type             | Null | Key | Default | Extra          |
+--------------+------------------+------+-----+---------+----------------+
| FieldHostUID | int(11)          | NO   | PRI | NULL    | auto_increment |
| ColEventUID  | int(11)          | NO   | MUL | 0       |                |
| HostNumber   | varchar(50)      | YES  |     | NULL    |                |
| FieldIdent   | varchar(50)      | YES  |     | NULL    |                |
| FieldNotes   | varchar(250)     | YES  |     | NULL    |                |
| CreateDate   | date             | YES  |     | NULL    |                |
| UpdateDate   | date             | YES  |     | NULL    |                |
| CreatedBy    | varchar(50)      | YES  |     | NULL    |                |
| UpdatedBy    | varchar(50)      | YES  |     | NULL    |                |
| TripUID      | int(11)          | NO   |     | 0       |                |
| HostF        | int(10) unsigned | NO   |     | 0       |                |
| HostG        | int(10) unsigned | NO   | MUL | 0       |                |
| HostSp       | int(10) unsigned | NO   |     | 0       |                |
| HostSSp      | int(10) unsigned | NO   |     | 0       |                |
| HostAuthor   | int(10) unsigned | NO   |     | 0       |                |
| HostDetBy    | varchar(255)     | YES  |     | NULL    |                |
| HerbID       | varchar(30)      | YES  |     | NULL    |                |
+--------------+------------------+------+-----+---------+----------------+_


+--------------+-------------+------+-----+------------+----------------+
| Field        | Type        | Null | Key | Default    | Extra          |
+--------------+-------------+------+-----+------------+----------------+
| HostMNLUID   | int(11)     | NO   | PRI | NULL       | auto_increment |
| HostTaxName  | varchar(50) | NO   | MUL |            |                |
| HostTaxLevel | varchar(15) | NO   | MUL | T          |                |
| HostParentID | int(11)     | NO   |     | 0          |                |
| HostAuthor   | varchar(50) | YES  |     |            |                |
| CreateDate   | date        | NO   |     | 0000-00-00 |                |
| UpdateDate   | date        | NO   |     | 0000-00-00 |                |
| CreatedBy    | varchar(50) | YES  |     | NULL       |                |
| UpdatedBy    | varchar(50) | YES  |     | NULL       |                |
+--------------+-------------+------+-----+------------+----------------+

Select F1.HostMNLUID, F3.HostTaxName,F2.HostTaxName,F1.HostTaxName,F1.HostAuthor from Flora_MNL F1 left join Flora_MNL F2 on F1.HostParentID=F2.HostMNLUID left join Flora_MNL F3 on F2.HostParentID=F3.HostMNLUID where F1.HostTaxName = 'angustissima' and F1.HostAuthor ='Jacq.' and F2.HostTaxName='viscosa' and F3.HostTaxName='Dodonaea';

Select F1.HostMNLUID, F2.HostTaxName, F1.HostTaxName, concat('no subspecies'),F1.HostAuthor from Flora_MNL F1 left join Flora_MNL F2 on F1.HostParentID=F2.HostMNLUID where F1.HostTaxName ='rosum' and F1.HostAuthor !='(P.J.Bergius) Less.' and F2.HostTaxName='Helichrysum'