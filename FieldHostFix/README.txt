Scripts for updating the fieldhost table to a b-tree and matching the flora table.

Notes:
select distinct FieldHost.HostG, F3.HostTaxName, FieldHost.HostSp,F1.HostTaxName as HostSpName, FieldHost.HostAuthor, F2.HostTaxName  as HostAuthor from FieldHost left join Flora_MNL F1 on FieldHost.HostSp=F1.HostMNLUID left join Flora_MNL F2 on FieldHost.HostAuthor=F2.HostMNLUID left join Flora_MNL F3 on FieldHost.HostG=F3.HostMNLUID where HostF='313' and F3.HostTaxName = 'Scholtzia' order by HostSpName;


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