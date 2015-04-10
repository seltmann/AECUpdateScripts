<?php
ini_set('include_path', '../includes' . PATH_SEPARATOR . ini_get("include_path"));
require_once("init.php");
require_once("MDB2.php");
#require_once("S_PBI.php");

// === Main ===
$DB =& MDB2::connect($CONFIG['root']['DB']['dsn']);
if (PEAR::isError($DB)) { handleError($DB->getMessage()); }

// Handle querystring
M_Validation::checkVars(array('UID' => 1), false);

//select from AEC database and insert into omoccurrences; symbscan2
	

SELECT
	

##########record-level terms#################
			NOW() as dcterms_modified,
			#Institution.InstName as dcterms_rightsHolder, #dcterms_rightsHolder
			#concat('Tri-Trophic Database Thematic Collection Network Project Dataset') as dwc_datasetName,
			Institution.InstCode as institutionCode, #scan:institutionCode
			concat('PreservedSpecimen') as basisOfRecord, #scan:basisOfRecord
			S1.UUID as occurrenceID, #scan:occurrenceID
			
			CASE #scan:catalogNumber
			WHEN S1.PBIUSI LIKE '%NCSUNCSU%' THEN CONCAT('NCSU', " ", RIGHT(S1.PBIUSI,7)) #format should be NCSU XXXXXXX
			WHEN S1.PBIUSI LIKE '%OSACOSAC%' THEN REPLACE(S1.PBIUSI, 'OSACOSAC ', 'OSAC00') #format should be OSACXXXXXXXXXX
			WHEN S1.PBIUSI LIKE '%EMECEMEC%' THEN CONCAT('EMEC',RIGHT(S1.PBIUSI,6)) #format should be NCSUXXXXXX
			WHEN S1.PBIUSI LIKE '%CASC_ENT%' THEN CONCAT('CASENT',RIGHT(S1.PBIUSI,7)) #format should be CASENTXXXXXXX
			WHEN S1.PBIUSI LIKE '%UCR_ENT%' THEN TRIM(S1.PBIUSI)
			#WHEN S1.PBIUSI LIKE '%KUNHMENT%' THEN REPLACE(S1.PBIUSI, 'KUNHMENT', 'KUNHM-ENT') #not sharing these
			ELSE ifnull(S1.PBIUSI, '')
			END as catalogNumber,  
			
			ifnull(Collector.CollName,'') as associatedCollectors, #scan:associatedCollectors
			S1.NumSpec as individualCount, #scan:individualCount

			CASE #scan:sex
			WHEN S1.Sex LIKE '%Female%' THEN 'Female'
			WHEN S1.Sex LIKE '%Male%' THEN 'Male'
			WHEN S1.Sex LIKE '%Mixed%' THEN 'Male and Female'
			ELSE ''
			END as sex,

			CASE #$scan:lifeStage
			WHEN S1.Sex LIKE '%Adult%' THEN 'Adult'
			WHEN S1.Sex LIKE '%Subadult%' THEN 'Juvenile'
			WHEN S1.Sex LIKE '%Juvenile%' THEN 'Juvenile'
			WHEN S1.Sex LIKE '%Egg%' THEN 'Egg'
			ELSE ''
			END as lifeStage,

			//             ifnull(F1.HostTaxName, '') as host_family,
			//             ifnull(trim(concat_ws(' ',
			//                                   ifnull(F2.HostTaxName, ''),
			//                                   ifnull(F3.HostTaxName, ''),
			//                                   ifnull(F4.HostTaxName, ''))), '') as dwc_associatedTaxa,
			// 
			// CASE
			// WHEN S1.HostSSp != '0' THEN ifnull(F4.HostAuthor, '')
			// WHEN S1.HostSp != '0' THEN ifnull(F3.HostAuthor, '')
			// ELSE ''
			// END as associate_author,
			// 
			// ifnull(HostCommonName.CommonName,'') as associatedCommonName,
			// ifnull(S1.HostNotes, '') as associatedNotes,
			// ifnull(S1.HostDetBy, '') as associatedDeterminedBy,
			// ifnull(S1.HerbID, '') as associatedOccurance, #not good for us; really needs to be included in notes
			// ifnull(S1.HostRel, '') as associatedRelationship,
			// ifnull(S1.Condition, '') as associatedCondition,
			// ifnull(S1.HostLoc, '') as associatedLocation,

			
			ifnull(S1.OrigUSI,'') as otherCatalogNumbers, #scan:otherCatalogNumbers
			
##########colecting event information################
			ifnull(S1.CollMeth, '') as dwc_samplingProtocol, #dwc:samplingProtocol
			concat(colevent.DateStart,"/",colevent.DateEnd) as dwc_eventDate,
			IF(colevent.DateStart !='0000-00-00',Substring(DateStart,-10,4),'') as dwc_year, #dwc:year
			ifnull(colevent.DateVerbatim,'') as dwc_verbatimEventDate, 	#dwc:verbatimEventDate
			#IF(H1.HabitatName != '' and H2.HabitatName != '',concat(ifnull(H1.HabitatName, ''),";",ifnull(H2.HabitatName, '')),concat(ifnull(H1.HabitatName, ''),ifnull(H2.HabitatName, ''))) as dwc_habitat,
		
###########locality information#################
            IF(Country.Country = 'USA', 'United States',Country.Country ) as dwc_country, #$dwc:country
            ifnull(StateProv.StateProv, '') as dwc_stateProvince, #$dwc:stateProvince
            SubDiv.SubDivStr as dwc_county, #$dwc:county
            Locality.LocalityStr as dwc_locality, #$dwc:locality
            IF(Locality.DLat !='0.00000',Locality.DLat,'') as dwc_decimalLatitude,#$dwc:decimalLatitude
            IF(Locality.Dlong !='0.00000',Locality.Dlong,'') as dwc_decimalLongitude,#$dwc:decimalLongitude
			Locality.LocAccuracy as dwc_coordinateUncertaintyInMeters, #$dwc:coordinatePrecision
			Locality.NNotes as dwc_georeferenceRemarks, #$dwc:georeferenceRemarks
			Locality.GeoRefMethod as dwc_locationAccordingTo, #$dwc:locationAccordingTo
        	IF(Locality.ElevM != '0',concat (Locality.ElevM, " m"),"") as dwc_verbatimElevation,#$dwc:verbatimElevation

##########identification#################
			ifnull(S1.TypeStatus, '') as dwc_typeStatus, #$dwc:typeStatus
			ifnull(S1.DetBy, '') as dwc_identifiedBy, #dwc:identifiedBy
			ifnull(S1.DeterminationVB, '') as determination_history, #past history
			ifnull(S1.DetDate, '') as dwc_dateIdentified, #dwc:dateIdentified

##########taxon name information#################
			concat('Animalia') as dwc_kingdom, #dwc:kingdom
			concat('Arthropoda') as dwc_phylum, #$dwc:phylum
			concat('Insecta') as dwc_class, #$dwc:class
			concat('Hemiptera') as dwc_order, #$dwc:order

			T5.TaxName as dwc_family, #$dwc:family

			CASE #$subfamily
			WHEN T4.TaxName like '%none%' THEN REPLACE(T4.TaxName,T4.TaxName,'')
			WHEN T4.TaxName like '%unknown%' THEN REPLACE(T4.TaxName,T4.TaxName,'')
			WHEN T4.TaxName like'%\_%' THEN REPLACE(T4.TaxName,T4.TaxName,'')
			ELSE T4.TaxName
			END as subfamily,

			CASE #$tribe
			WHEN T3.TaxName like '%none%' THEN REPLACE(T3.TaxName,T3.TaxName,'')
			WHEN T3.TaxName like'%unknown%' THEN REPLACE(T3.TaxName,T3.TaxName,'')
			WHEN T3.TaxName like'%\_%' THEN REPLACE(T3.TaxName,T3.TaxName,'')
			ELSE T3.TaxName
			END as tribe,

			CASE #dwc:higherClassification
			WHEN T4.TaxName LIKE '%none%' THEN concat_ws(";",'Animalia','Arthropoda','Insecta','Hemiptera',T5.TaxName,'Unknown',T3.TaxName)
			WHEN T3.TaxName LIKE '%none%' THEN concat_ws(";",'Animalia','Arthropoda','Insecta','Hemiptera',T5.TaxName,T4.TaxName,'Unknown')
			WHEN T3.TaxName LIKE '%none%' and T4.TaxName LIKE '%none%' THEN concat_ws(";",'Animalia','Arthropoda','Insecta','Hemiptera',T5.TaxName,'Unknown','Unknown')
			ELSE concat_ws(";",'Animalia','Arthropoda','Insecta','Hemiptera',T5.TaxName,T4.TaxName,T3.TaxName)
			END as dwc_higherClassification,

			CASE #$genus
			WHEN T1.TaxName like '%(%' THEN Genus(T1.TaxName)
			WHEN T1.TaxName like '%nov. gen%' THEN ''
			WHEN T1.TaxName like'%unknown%' THEN ''
			WHEN T1.TaxName like'%\_%' THEN ''
			ELSE T1.TaxName
			END as dwc_genus,

			CASE #$subgenus
			WHEN T1.TaxName like '%(%' THEN SubGenus(T1.TaxName)
			WHEN T1.TaxName like '%nov. gen%' THEN ''
			WHEN T1.TaxName like'%unknown%' THEN ''
			WHEN T1.TaxName like'%\_%' THEN ''
			ELSE ''
			END as subgenus,

			CASE #$dwc:specificEpithet
			WHEN T2.TaxName='sp' THEN ''
			WHEN T2.TaxName like '%ssp.%' THEN Species(T2.TaxName)
			WHEN T2.TaxName='manuscript' THEN ''
			WHEN T2.TaxName like '%#%' THEN ''
			WHEN T2.TaxName like '%\(%' THEN ''
			WHEN T2.TaxName like '%sp.%' THEN ''
			WHEN T2.TaxName='unknown' THEN ''
			WHEN T2.TaxName like'%\_%' THEN ''
			WHEN T2.TaxName like '%spp.%' THEN ''
			WHEN T2.TaxName like 'nr.' THEN ''
			WHEN T1.TaxName like '%nov. gen%' THEN REPLACE(T2.TaxName,T2.TaxName,'')
			WHEN T1.TaxName like'%unknown%' THEN REPLACE(T2.TaxName,T2.TaxName,'')
			WHEN T1.TaxName like'%\_%' THEN REPLACE(T2.TaxName,T2.TaxName,'')
			ELSE T2.TaxName
			END as dwc_specificEpithet,
			

			CASE #dwc_infraspecificEpithet
			WHEN T2.TaxName like '%ssp.%' THEN SubSpecies(T2.TaxName)
			WHEN T2.TaxName='manuscript' THEN ''
			WHEN T2.TaxName like '%#%' THEN ''
			WHEN T2.TaxName like '%\(%' THEN ''
			WHEN T2.TaxName like '%sp.%' THEN ''
			WHEN T2.TaxName='unknown' THEN ''
			WHEN T2.TaxName like'%\_%' THEN ''
			WHEN T2.TaxName like '%spp.%' THEN ''
			WHEN T2.TaxName like 'nr.' THEN ''
			ELSE ''
			END as dwc_infraspecificEpithet,

			IF(T2.AuthorName like '%\(%',REPLACE(T2.AuthorName, ')',concat(",", T2.AuthorDate,"\)")),IF(T2.AuthorName != '',concat(T2.AuthorName,',',T2.AuthorDate),"")) as dwc_scientificNameAuthorship #dwc:scientificNameAuthorship for species
			#dwc_taxonRank #write functions
			
			
				
FROM Specimen S1

left join Flora_MNL F1 ON S1.HostF = F1.HostMNLUID
left join Flora_MNL F2  ON S1.HostG = F2.HostMNLUID
left join Flora_MNL F3  ON S1.HostSp = F3.HostMNLUID
left join Flora_MNL F4  ON S1.HostSSp = F4.HostMNLUID

left join MNL T1  ON S1.Genus = T1.MNLUID
left join MNL T2  ON S1.Species = T2.MNLUID
left join MNL T3  ON S1.Tribe = T3.MNLUID
left join MNL T4  ON S1.Subfamily = T4.MNLUID
left join MNL T5  ON T4.ParentID = T5.MNLUID

left join colevent on S1.ColEventUID = colevent.ColEventUID
left join Collector on colevent.Collector = Collector.CollectorUID

left join Locality on S1.Locality = Locality.LocalityUID
left join SubDiv on Locality.SubDivUID = SubDiv.SubDivUID
left join StateProv on SubDiv.StateProvUID = StateProv.StateProvUID
left join Country on StateProv.CountryUID = Country.UID
left join Institution on S1.InstUID = Institution.InstUID
left join HostCommonName on S1.HostCName = HostCommonName.CommonUID
left join Habitat H1 on S1.MacroUID = H1.HabitatUID
left join Habitat H2 on S1.MicroUID = H2.HabitatUID
left join UUser on S1.CreatedBy=UUser.UserName 

where S1.Insect_ID = 1 and S1.PBIUSI !='' and S1.PBIUSI !='0' and CHAR_LENGTH(S1.PBIUSI) > 9 and (Country.UID='8' OR Country.UID='11' OR Country.UID='2') and S1.mapping = 0 and S1.CreateDate > '2011-07-01' and UUser.Initials like '%TTD-TCN%' and S1.PBIUSI NOT LIKE '%KUNHMENT%' GROUP BY idigbio_barcodeValue HAVING idigbio_barcodeValue < 2 order by S1.SpecimenUID desc limit 200;

#These are additional mysql functions that are necessary to run this query
# CREATE FUNCTION SubGenus (TaxName TEXT)
# RETURNS TEXT
# BEGIN
# DECLARE var1  INT;
# DECLARE var2  INT;
# DECLARE var3  INT;
# DECLARE var4  INT;
# SELECT LOCATE('\(', TaxName) INTO @var1;
# SELECT LOCATE('\)', TaxName) INTO @var2;
# SET @var3 := @var1 + 1;
# SET @var4 := @var2 - @var3;
# RETURN SUBSTRING(TRIM(TaxName), @var3, @var4);
# END$$
# 
# 
# CREATE FUNCTION Genus (TaxName TEXT)
# RETURNS TEXT
# BEGIN
# DECLARE var1  INT;
# DECLARE var3  INT;
# SELECT LOCATE('\(', TaxName) INTO @var1;
# SET @var3 := @var1 - 1;
# RETURN SUBSTRING(TRIM(TaxName), 1, @var3);
# END$$
# 
# CREATE FUNCTION Species (TaxName TEXT)
# RETURNS TEXT
# BEGIN
# DECLARE var1  INT;
# DECLARE var3  INT;
# SELECT LOCATE('ssp.', TaxName) INTO @var1;
# SET @var3 := @var1 - 1;
# RETURN SUBSTRING(TRIM(TaxName), 1, @var3);
# END$$
# 
# CREATE FUNCTION SubSpecies (TaxName TEXT)
# RETURNS TEXT
# BEGIN
# DECLARE var1  INT;
# DECLARE var3  INT;
# SELECT LOCATE('ssp.', TaxName) INTO @var1;
# SET @var3 := @var1 + 4;
# RETURN TRIM(SUBSTRING(TRIM(TaxName), @var3));
# END$$
?>