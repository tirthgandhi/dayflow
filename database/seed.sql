-- ============================================================
-- Multi-Company HRMS Comprehensive Seed Data
-- 50 Companies with 50+ Employees Each
-- ============================================================

USE hrms_db;

-- ============================================================
-- SAMPLE COMPANIES (50 Companies across various industries)
-- ============================================================

INSERT INTO companies (name, registration_number, email, phone, address, city, state, country, postal_code, website, industry, company_size, logo_path, logo_url, status, subscription_plan, subscription_expires) VALUES
-- Technology Companies (1-10)
('TechCorp Solutions', 'TC-2024-001', 'admin@techcorp.com', '+1-555-0101', '123 Tech Street', 'San Francisco', 'California', 'USA', '94102', 'https://techcorp.com', 'Technology', '201-500', 'logos/techcorp.png', 'https://ui-avatars.com/api/?name=TechCorp+Solutions&background=0D8ABC&color=fff&size=128', 'active', 'enterprise', '2027-12-31'),
('InnovateTech Inc', 'IT-2024-002', 'admin@innovatetech.com', '+1-555-0102', '456 Innovation Blvd', 'Austin', 'Texas', 'USA', '78701', 'https://innovatetech.com', 'Technology', '51-200', 'logos/innovatetech.png', 'https://ui-avatars.com/api/?name=InnovateTech+Inc&background=2E86AB&color=fff&size=128', 'active', 'professional', '2027-06-30'),
('CloudNine Systems', 'CN-2024-003', 'admin@cloudnine.com', '+1-555-0103', '789 Cloud Ave', 'Seattle', 'Washington', 'USA', '98101', 'https://cloudnine.com', 'Technology', '201-500', 'logos/cloudnine.png', 'https://ui-avatars.com/api/?name=CloudNine+Systems&background=A23B72&color=fff&size=128', 'active', 'enterprise', '2027-12-31'),
('DataDriven Analytics', 'DD-2024-004', 'admin@datadriven.com', '+1-555-0104', '321 Data Lane', 'Boston', 'Massachusetts', 'USA', '02101', 'https://datadriven.com', 'Technology', '51-200', 'logos/datadriven.png', 'https://ui-avatars.com/api/?name=DataDriven+Analytics&background=F18F01&color=fff&size=128', 'active', 'professional', '2027-09-30'),
('CyberShield Security', 'CS-2024-005', 'admin@cybershield.com', '+1-555-0105', '555 Security Blvd', 'Denver', 'Colorado', 'USA', '80202', 'https://cybershield.com', 'Technology', '51-200', 'logos/cybershield.png', 'https://ui-avatars.com/api/?name=CyberShield+Security&background=C73E1D&color=fff&size=128', 'active', 'professional', '2027-08-31'),
('AppWorks Studio', 'AW-2024-006', 'admin@appworks.com', '+1-555-0106', '888 App Street', 'Portland', 'Oregon', 'USA', '97201', 'https://appworks.com', 'Technology', '11-50', 'logos/appworks.png', 'https://ui-avatars.com/api/?name=AppWorks+Studio&background=3D5A80&color=fff&size=128', 'active', 'basic', '2027-03-31'),
('QuantumByte Labs', 'QB-2024-007', 'admin@quantumbyte.com', '+1-555-0107', '999 Quantum Road', 'San Jose', 'California', 'USA', '95101', 'https://quantumbyte.com', 'Technology', '201-500', 'logos/quantumbyte.png', 'https://ui-avatars.com/api/?name=QuantumByte+Labs&background=293241&color=fff&size=128', 'active', 'enterprise', '2027-12-31'),
('NexGen Software', 'NG-2024-008', 'admin@nexgen.com', '+1-555-0108', '111 Next Ave', 'Chicago', 'Illinois', 'USA', '60601', 'https://nexgen.com', 'Technology', '51-200', 'logos/nexgen.png', 'https://ui-avatars.com/api/?name=NexGen+Software&background=98C1D9&color=000&size=128', 'active', 'professional', '2027-07-31'),
('PixelPerfect Design', 'PP-2024-009', 'admin@pixelperfect.com', '+1-555-0109', '222 Pixel Lane', 'Los Angeles', 'California', 'USA', '90001', 'https://pixelperfect.com', 'Technology', '11-50', 'logos/pixelperfect.png', 'https://ui-avatars.com/api/?name=PixelPerfect+Design&background=EE6C4D&color=fff&size=128', 'active', 'basic', '2027-04-30'),
('SmartAI Solutions', 'SA-2024-010', 'admin@smartai.com', '+1-555-0110', '333 AI Boulevard', 'Palo Alto', 'California', 'USA', '94301', 'https://smartai.com', 'Technology', '51-200', 'logos/smartai.png', 'https://ui-avatars.com/api/?name=SmartAI+Solutions&background=5C4D7D&color=fff&size=128', 'active', 'professional', '2027-10-31'),

-- Healthcare Companies (11-20)
('HealthCare Plus', 'HC-2024-011', 'admin@healthcareplus.com', '+1-555-0111', '444 Medical Center Dr', 'Boston', 'Massachusetts', 'USA', '02102', 'https://healthcareplus.com', 'Healthcare', '501-1000', 'logos/healthcareplus.png', 'https://ui-avatars.com/api/?name=HealthCare+Plus&background=2A9D8F&color=fff&size=128', 'active', 'enterprise', '2027-12-31'),
('MediCare Systems', 'MC-2024-012', 'admin@medicare-sys.com', '+1-555-0112', '555 Health Ave', 'Philadelphia', 'Pennsylvania', 'USA', '19101', 'https://medicare-sys.com', 'Healthcare', '201-500', 'logos/medicare.png', 'https://ui-avatars.com/api/?name=MediCare+Systems&background=264653&color=fff&size=128', 'active', 'enterprise', '2027-11-30'),
('WellnessFirst Clinic', 'WF-2024-013', 'admin@wellnessfirst.com', '+1-555-0113', '666 Wellness Blvd', 'Miami', 'Florida', 'USA', '33101', 'https://wellnessfirst.com', 'Healthcare', '51-200', 'logos/wellnessfirst.png', 'https://ui-avatars.com/api/?name=WellnessFirst+Clinic&background=E9C46A&color=000&size=128', 'active', 'professional', '2027-08-31'),
('BioMed Research', 'BM-2024-014', 'admin@biomed.com', '+1-555-0114', '777 Research Park', 'San Diego', 'California', 'USA', '92101', 'https://biomed.com', 'Healthcare', '201-500', 'logos/biomed.png', 'https://ui-avatars.com/api/?name=BioMed+Research&background=F4A261&color=000&size=128', 'active', 'enterprise', '2027-12-31'),
('CareConnect Health', 'CC-2024-015', 'admin@careconnect.com', '+1-555-0115', '888 Care Street', 'Houston', 'Texas', 'USA', '77001', 'https://careconnect.com', 'Healthcare', '51-200', 'logos/careconnect.png', 'https://ui-avatars.com/api/?name=CareConnect+Health&background=E76F51&color=fff&size=128', 'active', 'professional', '2027-09-30'),
('PharmaTech Labs', 'PT-2024-016', 'admin@pharmatech.com', '+1-555-0116', '999 Pharma Road', 'New Jersey', 'New Jersey', 'USA', '07001', 'https://pharmatech.com', 'Healthcare', '201-500', 'logos/pharmatech.png', 'https://ui-avatars.com/api/?name=PharmaTech+Labs&background=606C38&color=fff&size=128', 'active', 'enterprise', '2027-12-31'),
('LifeLine Medical', 'LL-2024-017', 'admin@lifeline.com', '+1-555-0117', '100 Life Ave', 'Atlanta', 'Georgia', 'USA', '30301', 'https://lifeline.com', 'Healthcare', '51-200', 'logos/lifeline.png', 'https://ui-avatars.com/api/?name=LifeLine+Medical&background=283618&color=fff&size=128', 'active', 'professional', '2027-07-31'),
('NeuroHealth Institute', 'NH-2024-018', 'admin@neurohealth.com', '+1-555-0118', '200 Neuro Blvd', 'Baltimore', 'Maryland', 'USA', '21201', 'https://neurohealth.com', 'Healthcare', '51-200', 'logos/neurohealth.png', 'https://ui-avatars.com/api/?name=NeuroHealth+Institute&background=FEFAE0&color=000&size=128', 'active', 'professional', '2027-06-30'),
('CardioFirst Center', 'CF-2024-019', 'admin@cardiofirst.com', '+1-555-0119', '300 Heart Lane', 'Cleveland', 'Ohio', 'USA', '44101', 'https://cardiofirst.com', 'Healthcare', '51-200', 'logos/cardiofirst.png', 'https://ui-avatars.com/api/?name=CardioFirst+Center&background=DDA15E&color=000&size=128', 'active', 'professional', '2027-05-31'),
('GenomeX Diagnostics', 'GX-2024-020', 'admin@genomex.com', '+1-555-0120', '400 Genome Street', 'Cambridge', 'Massachusetts', 'USA', '02139', 'https://genomex.com', 'Healthcare', '51-200', 'logos/genomex.png', 'https://ui-avatars.com/api/?name=GenomeX+Diagnostics&background=BC6C25&color=fff&size=128', 'active', 'professional', '2027-10-31'),

-- Finance Companies (21-30)
('GlobalFinance Corp', 'GF-2024-021', 'admin@globalfinance.com', '+1-555-0121', '500 Finance Tower', 'New York', 'New York', 'USA', '10001', 'https://globalfinance.com', 'Finance', '501-1000', 'logos/globalfinance.png', 'https://ui-avatars.com/api/?name=GlobalFinance+Corp&background=003049&color=fff&size=128', 'active', 'enterprise', '2027-12-31'),
('SecureBank Holdings', 'SB-2024-022', 'admin@securebank.com', '+1-555-0122', '600 Bank Street', 'Charlotte', 'North Carolina', 'USA', '28201', 'https://securebank.com', 'Finance', '201-500', 'logos/securebank.png', 'https://ui-avatars.com/api/?name=SecureBank+Holdings&background=D62828&color=fff&size=128', 'active', 'enterprise', '2027-11-30'),
('InvestPro Advisors', 'IP-2024-023', 'admin@investpro.com', '+1-555-0123', '700 Investment Ave', 'Chicago', 'Illinois', 'USA', '60602', 'https://investpro.com', 'Finance', '51-200', 'logos/investpro.png', 'https://ui-avatars.com/api/?name=InvestPro+Advisors&background=F77F00&color=fff&size=128', 'active', 'professional', '2027-09-30'),
('WealthWise Management', 'WW-2024-024', 'admin@wealthwise.com', '+1-555-0124', '800 Wealth Blvd', 'San Francisco', 'California', 'USA', '94103', 'https://wealthwise.com', 'Finance', '51-200', 'logos/wealthwise.png', 'https://ui-avatars.com/api/?name=WealthWise+Management&background=FCBF49&color=000&size=128', 'active', 'professional', '2027-08-31'),
('CreditFirst Services', 'CFS-2024-025', 'admin@creditfirst.com', '+1-555-0125', '900 Credit Lane', 'Dallas', 'Texas', 'USA', '75201', 'https://creditfirst.com', 'Finance', '51-200', 'logos/creditfirst.png', 'https://ui-avatars.com/api/?name=CreditFirst+Services&background=EAE2B7&color=000&size=128', 'active', 'professional', '2027-07-31'),
('TradeMaster Exchange', 'TM-2024-026', 'admin@trademaster.com', '+1-555-0126', '1000 Trade Center', 'New York', 'New York', 'USA', '10002', 'https://trademaster.com', 'Finance', '201-500', 'logos/trademaster.png', 'https://ui-avatars.com/api/?name=TradeMaster+Exchange&background=001219&color=fff&size=128', 'active', 'enterprise', '2027-12-31'),
('InsureAll Group', 'IA-2024-027', 'admin@insureall.com', '+1-555-0127', '1100 Insurance Blvd', 'Hartford', 'Connecticut', 'USA', '06101', 'https://insureall.com', 'Finance', '201-500', 'logos/insureall.png', 'https://ui-avatars.com/api/?name=InsureAll+Group&background=005F73&color=fff&size=128', 'active', 'enterprise', '2027-11-30'),
('CapitalGrowth Partners', 'CG-2024-028', 'admin@capitalgrowth.com', '+1-555-0128', '1200 Capital Street', 'Boston', 'Massachusetts', 'USA', '02103', 'https://capitalgrowth.com', 'Finance', '51-200', 'logos/capitalgrowth.png', 'https://ui-avatars.com/api/?name=CapitalGrowth+Partners&background=0A9396&color=fff&size=128', 'active', 'professional', '2027-10-31'),
('PaymentPro Solutions', 'PPS-2024-029', 'admin@paymentpro.com', '+1-555-0129', '1300 Payment Ave', 'Atlanta', 'Georgia', 'USA', '30302', 'https://paymentpro.com', 'Finance', '51-200', 'logos/paymentpro.png', 'https://ui-avatars.com/api/?name=PaymentPro+Solutions&background=94D2BD&color=000&size=128', 'active', 'professional', '2027-09-30'),
('AssetGuard Trust', 'AG-2024-030', 'admin@assetguard.com', '+1-555-0130', '1400 Asset Lane', 'Phoenix', 'Arizona', 'USA', '85001', 'https://assetguard.com', 'Finance', '51-200', 'logos/assetguard.png', 'https://ui-avatars.com/api/?name=AssetGuard+Trust&background=E9D8A6&color=000&size=128', 'active', 'professional', '2027-08-31'),

-- Retail Companies (31-40)
('GlobalRetail Inc', 'GR-2024-031', 'admin@globalretail.com', '+1-555-0131', '1500 Retail Plaza', 'New York', 'New York', 'USA', '10003', 'https://globalretail.com', 'Retail', '1000+', 'logos/globalretail.png', 'https://ui-avatars.com/api/?name=GlobalRetail+Inc&background=EE9B00&color=000&size=128', 'active', 'enterprise', '2027-12-31'),
('FashionForward Stores', 'FF-2024-032', 'admin@fashionforward.com', '+1-555-0132', '1600 Fashion Ave', 'Los Angeles', 'California', 'USA', '90002', 'https://fashionforward.com', 'Retail', '201-500', 'logos/fashionforward.png', 'https://ui-avatars.com/api/?name=FashionForward+Stores&background=CA6702&color=fff&size=128', 'active', 'enterprise', '2027-11-30'),
('HomeGoods Depot', 'HG-2024-033', 'admin@homegoods.com', '+1-555-0133', '1700 Home Street', 'Atlanta', 'Georgia', 'USA', '30303', 'https://homegoods.com', 'Retail', '501-1000', 'logos/homegoods.png', 'https://ui-avatars.com/api/?name=HomeGoods+Depot&background=BB3E03&color=fff&size=128', 'active', 'enterprise', '2027-12-31'),
('ElectroMart Chain', 'EM-2024-034', 'admin@electromart.com', '+1-555-0134', '1800 Electronics Blvd', 'Dallas', 'Texas', 'USA', '75202', 'https://electromart.com', 'Retail', '201-500', 'logos/electromart.png', 'https://ui-avatars.com/api/?name=ElectroMart+Chain&background=AE2012&color=fff&size=128', 'active', 'enterprise', '2027-10-31'),
('GroceryKing Markets', 'GK-2024-035', 'admin@groceryking.com', '+1-555-0135', '1900 Grocery Lane', 'Chicago', 'Illinois', 'USA', '60603', 'https://groceryking.com', 'Retail', '1000+', 'logos/groceryking.png', 'https://ui-avatars.com/api/?name=GroceryKing+Markets&background=9B2226&color=fff&size=128', 'active', 'enterprise', '2027-12-31'),
('SportZone Outlets', 'SZ-2024-036', 'admin@sportzone.com', '+1-555-0136', '2000 Sports Ave', 'Denver', 'Colorado', 'USA', '80203', 'https://sportzone.com', 'Retail', '51-200', 'logos/sportzone.png', 'https://ui-avatars.com/api/?name=SportZone+Outlets&background=582F0E&color=fff&size=128', 'active', 'professional', '2027-09-30'),
('BookWorld Stores', 'BW-2024-037', 'admin@bookworld.com', '+1-555-0137', '2100 Book Street', 'Seattle', 'Washington', 'USA', '98102', 'https://bookworld.com', 'Retail', '51-200', 'logos/bookworld.png', 'https://ui-avatars.com/api/?name=BookWorld+Stores&background=7F4F24&color=fff&size=128', 'active', 'professional', '2027-08-31'),
('PetParadise Shops', 'PPS-2024-038', 'admin@petparadise.com', '+1-555-0138', '2200 Pet Lane', 'Phoenix', 'Arizona', 'USA', '85002', 'https://petparadise.com', 'Retail', '51-200', 'logos/petparadise.png', 'https://ui-avatars.com/api/?name=PetParadise+Shops&background=936639&color=fff&size=128', 'active', 'professional', '2027-07-31'),
('ToyLand Express', 'TL-2024-039', 'admin@toyland.com', '+1-555-0139', '2300 Toy Blvd', 'Orlando', 'Florida', 'USA', '32801', 'https://toyland.com', 'Retail', '51-200', 'logos/toyland.png', 'https://ui-avatars.com/api/?name=ToyLand+Express&background=A68A64&color=fff&size=128', 'active', 'professional', '2027-06-30'),
('GardenCenter Plus', 'GC-2024-040', 'admin@gardencenter.com', '+1-555-0140', '2400 Garden Road', 'Portland', 'Oregon', 'USA', '97202', 'https://gardencenter.com', 'Retail', '51-200', 'logos/gardencenter.png', 'https://ui-avatars.com/api/?name=GardenCenter+Plus&background=B6AD90&color=000&size=128', 'active', 'professional', '2027-05-31'),

-- Manufacturing Companies (41-50)
('SteelWorks Industries', 'SW-2024-041', 'admin@steelworks.com', '+1-555-0141', '2500 Steel Ave', 'Pittsburgh', 'Pennsylvania', 'USA', '15201', 'https://steelworks.com', 'Manufacturing', '501-1000', 'logos/steelworks.png', 'https://ui-avatars.com/api/?name=SteelWorks+Industries&background=344E41&color=fff&size=128', 'active', 'enterprise', '2027-12-31'),
('AutoParts Global', 'AP-2024-042', 'admin@autoparts.com', '+1-555-0142', '2600 Auto Blvd', 'Detroit', 'Michigan', 'USA', '48201', 'https://autoparts.com', 'Manufacturing', '201-500', 'logos/autoparts.png', 'https://ui-avatars.com/api/?name=AutoParts+Global&background=3A5A40&color=fff&size=128', 'active', 'enterprise', '2027-11-30'),
('ChemTech Solutions', 'CT-2024-043', 'admin@chemtech.com', '+1-555-0143', '2700 Chemical Road', 'Houston', 'Texas', 'USA', '77002', 'https://chemtech.com', 'Manufacturing', '201-500', 'logos/chemtech.png', 'https://ui-avatars.com/api/?name=ChemTech+Solutions&background=588157&color=fff&size=128', 'active', 'enterprise', '2027-12-31'),
('PackagePro Systems', 'PKP-2024-044', 'admin@packagepro.com', '+1-555-0144', '2800 Package Lane', 'Memphis', 'Tennessee', 'USA', '38101', 'https://packagepro.com', 'Manufacturing', '51-200', 'logos/packagepro.png', 'https://ui-avatars.com/api/?name=PackagePro+Systems&background=A3B18A&color=000&size=128', 'active', 'professional', '2027-10-31'),
('TextileMaster Corp', 'TXM-2024-045', 'admin@textilemaster.com', '+1-555-0145', '2900 Textile Street', 'Charlotte', 'North Carolina', 'USA', '28202', 'https://textilemaster.com', 'Manufacturing', '201-500', 'logos/textilemaster.png', 'https://ui-avatars.com/api/?name=TextileMaster+Corp&background=DAD7CD&color=000&size=128', 'active', 'enterprise', '2027-09-30'),
('FoodProcess Inc', 'FP-2024-046', 'admin@foodprocess.com', '+1-555-0146', '3000 Food Ave', 'Kansas City', 'Missouri', 'USA', '64101', 'https://foodprocess.com', 'Manufacturing', '201-500', 'logos/foodprocess.png', 'https://ui-avatars.com/api/?name=FoodProcess+Inc&background=6B705C&color=fff&size=128', 'active', 'enterprise', '2027-08-31'),
('PlasticWorks Ltd', 'PW-2024-047', 'admin@plasticworks.com', '+1-555-0147', '3100 Plastic Blvd', 'Indianapolis', 'Indiana', 'USA', '46201', 'https://plasticworks.com', 'Manufacturing', '51-200', 'logos/plasticworks.png', 'https://ui-avatars.com/api/?name=PlasticWorks+Ltd&background=A5A58D&color=000&size=128', 'active', 'professional', '2027-07-31'),
('MetalCraft Industries', 'MCI-2024-048', 'admin@metalcraft.com', '+1-555-0148', '3200 Metal Road', 'Milwaukee', 'Wisconsin', 'USA', '53201', 'https://metalcraft.com', 'Manufacturing', '51-200', 'logos/metalcraft.png', 'https://ui-avatars.com/api/?name=MetalCraft+Industries&background=B7B7A4&color=000&size=128', 'active', 'professional', '2027-06-30'),
('WoodWorks Premium', 'WWP-2024-049', 'admin@woodworks.com', '+1-555-0149', '3300 Wood Lane', 'Nashville', 'Tennessee', 'USA', '37201', 'https://woodworks.com', 'Manufacturing', '51-200', 'logos/woodworks.png', 'https://ui-avatars.com/api/?name=WoodWorks+Premium&background=FFE8D6&color=000&size=128', 'active', 'professional', '2027-05-31'),
('GlassTech Innovations', 'GT-2024-050', 'admin@glasstech.com', '+1-555-0150', '3400 Glass Street', 'Toledo', 'Ohio', 'USA', '43601', 'https://glasstech.com', 'Manufacturing', '51-200', 'logos/glasstech.png', 'https://ui-avatars.com/api/?name=GlassTech+Innovations&background=DDBEA9&color=000&size=128', 'active', 'professional', '2027-04-30');


-- ============================================================
-- LEAVE TYPES (Comprehensive leave policies per company)
-- ============================================================

-- Create leave types for all 50 companies using a procedure
DELIMITER //
CREATE PROCEDURE create_leave_types()
BEGIN
    DECLARE i INT DEFAULT 1;
    WHILE i <= 50 DO
        INSERT INTO leave_types (company_id, name, annual_allocation, is_paid, is_active) VALUES
        (i, 'Annual Leave', FLOOR(15 + RAND() * 10), 1, 1),
        (i, 'Sick Leave', FLOOR(8 + RAND() * 7), 1, 1),
        (i, 'Personal Leave', FLOOR(3 + RAND() * 5), 1, 1),
        (i, 'Maternity Leave', 90, 1, 1),
        (i, 'Paternity Leave', FLOOR(10 + RAND() * 10), 1, 1),
        (i, 'Bereavement Leave', 5, 1, 1),
        (i, 'Unpaid Leave', 30, 0, 1),
        (i, 'Study Leave', FLOOR(5 + RAND() * 10), 1, 1),
        (i, 'Jury Duty', 10, 1, 1),
        (i, 'Compensatory Off', FLOOR(5 + RAND() * 5), 1, 1);
        SET i = i + 1;
    END WHILE;
END //
DELIMITER ;

CALL create_leave_types();
DROP PROCEDURE create_leave_types;

-- ============================================================
-- USERS AND EMPLOYEES (50+ per company)
-- Using stored procedure for bulk generation
-- ============================================================

-- First names pool
SET @first_names = 'James,John,Robert,Michael,William,David,Richard,Joseph,Thomas,Charles,Christopher,Daniel,Matthew,Anthony,Mark,Donald,Steven,Paul,Andrew,Joshua,Kenneth,Kevin,Brian,George,Timothy,Ronald,Edward,Jason,Jeffrey,Ryan,Jacob,Gary,Nicholas,Eric,Jonathan,Stephen,Larry,Justin,Scott,Brandon,Benjamin,Samuel,Raymond,Gregory,Frank,Alexander,Patrick,Jack,Dennis,Jerry,Tyler,Aaron,Jose,Adam,Nathan,Henry,Douglas,Zachary,Peter,Kyle,Noah,Ethan,Jeremy,Walter,Christian,Keith,Roger,Terry,Austin,Sean,Gerald,Carl,Harold,Dylan,Arthur,Lawrence,Jordan,Jesse,Bryan,Billy,Bruce,Gabriel,Joe,Logan,Albert,Willie,Alan,Eugene,Russell,Vincent,Philip,Bobby,Johnny,Bradley,Mary,Patricia,Jennifer,Linda,Elizabeth,Barbara,Susan,Jessica,Sarah,Karen,Lisa,Nancy,Betty,Margaret,Sandra,Ashley,Kimberly,Emily,Donna,Michelle,Dorothy,Carol,Amanda,Melissa,Deborah,Stephanie,Rebecca,Sharon,Laura,Cynthia,Kathleen,Amy,Angela,Shirley,Anna,Brenda,Pamela,Emma,Nicole,Helen,Samantha,Katherine,Christine,Debra,Rachel,Carolyn,Janet,Catherine,Maria,Heather,Diane,Ruth,Julie,Olivia,Joyce,Virginia,Victoria,Kelly,Lauren,Christina,Joan,Evelyn,Judith,Megan,Andrea,Cheryl,Hannah,Jacqueline,Martha,Gloria,Teresa,Ann,Sara,Madison,Frances,Kathryn,Janice,Jean,Abigail,Alice,Judy,Sophia,Grace,Denise,Amber,Doris,Marilyn,Danielle,Beverly,Isabella,Theresa,Diana,Natalie,Brittany,Charlotte,Marie,Kayla,Alexis,Lori';

-- Last names pool  
SET @last_names = 'Smith,Johnson,Williams,Brown,Jones,Garcia,Miller,Davis,Rodriguez,Martinez,Hernandez,Lopez,Gonzalez,Wilson,Anderson,Thomas,Taylor,Moore,Jackson,Martin,Lee,Perez,Thompson,White,Harris,Sanchez,Clark,Ramirez,Lewis,Robinson,Walker,Young,Allen,King,Wright,Scott,Torres,Nguyen,Hill,Flores,Green,Adams,Nelson,Baker,Hall,Rivera,Campbell,Mitchell,Carter,Roberts,Gomez,Phillips,Evans,Turner,Diaz,Parker,Cruz,Edwards,Collins,Reyes,Stewart,Morris,Morales,Murphy,Cook,Rogers,Gutierrez,Ortiz,Morgan,Cooper,Peterson,Bailey,Reed,Kelly,Howard,Ramos,Kim,Cox,Ward,Richardson,Watson,Brooks,Chavez,Wood,James,Bennett,Gray,Mendoza,Ruiz,Hughes,Price,Alvarez,Castillo,Sanders,Patel,Myers,Long,Ross,Foster,Jimenez,Powell,Jenkins,Perry,Russell,Sullivan,Bell,Coleman,Butler,Henderson,Barnes,Gonzales,Fisher,Vasquez,Simmons,Stokes,Simpson,Crawford,Jimenez,Knight,Olson,Stone,Hart,Hunt,Palmer,Wagner,Freeman,Wells,Webb,Hamilton,Lawrence,Elliott,Fox,Medina,Rice,Alexander,Fernandez,Gibson,McDonald,Woods,Reynolds,Washington,Kennedy,Wells,Vargas,Henry,Chen,Freeman,Webb,Tucker,Guzman,Burns,Crawford,Olson,Simpson,Porter,Hunter,Gordon,Mendez,Silva,Shaw,Snyder,Mason,Dixon,Munoz,Hunt,Hicks,Holmes,Palmer,Wagner,Black,Robertson,Boyd,Rose,Stone,Salazar,Fox,Warren,Mills,Meyer,Rice,Schmidt,Garza,Daniels,Ferguson,Nichols,Stephens,Soto,Weaver,Ryan,Gardner,Payne,Grant,Dunn,Kelley,Spencer,Hawkins,Arnold,Pierce,Vazquez,Hansen,Peters,Santos,Hart,Bradley,Knight,Elliott,Cunningham,Duncan,Armstrong,Hudson,Carroll,Lane,Riley,Andrews,Alvarado,Ray,Delgado,Berry,Perkins,Hoffman,Johnston,Matthews,Pena,Richards,Contreras,Willis,Carpenter,Lawrence,Sandoval,Guerrero,George,Chapman,Rios,Estrada,Ortega,Watkins,Greene,Nunez,Wheeler,Valdez,Harper,Burke,Larson,Santiago,Maldonado,Morrison,Franklin,Carlson,Austin,Dominguez,Carr,Lawson,Jacobs,Obrien,Lynch,Singh,Vega,Bishop,Montgomery,Oliver,Jensen,Harvey,Williamson,Gilbert,Dean,Sims,Espinoza,Howell,Li,Wong,Reid,Hanson,Le,McCoy,Garrett,Burton,Fuller,Wang,Weber,Welch,Rojas,Lucas,Marquez,Fields,Park,Yang,Little,Banks,Padilla,Day,Walsh,Bowman,Schultz,Luna,Fowler,Mejia,Davidson,Acosta,Brewer,May,Holland,Juarez,Newman,Pearson,Curtis,Cortez,Douglas,Schneider,Joseph,Barrett,Navarro,Figueroa,Keller,Avila,Wade,Molina,Stanley,Hopkins,Campos,Barnett,Bates,Chambers,Caldwell,Beck,Lambert,Miranda,Byrd,Craig,Ayala,Lowe,Frazier,Powers,Neal,Leonard,Gregory,Carrillo,Sutton,Fleming,Rhodes,Shelton,Schwartz,Norris,Jennings,Watts,Duran,Walters,Cohen,McDaniel,Moran,Parks,Steele,Vaughn,Becker,Holt,DeLeon,Barker,Terry,Hale,Leon,Hail,Benson,Haynes,Horton,Miles,Lyons,Pham,Graves,Bush,Thornton,Wolfe,Warner,Cabrera,McKinney,Mann,Zimmerman,Dawson,Lara,Fletcher,Page,McCarthy,Love,Robles,Cervantes,Solis,Erickson,Reeves,Chang,Klein,Salinas,Fuentes,Baldwin,Daniel,Simon,Velasquez,Hardy,Higgins,Aguirre,Lin,Cummings,Chandler,Sharp,Barber,Bowen,Ochoa,Dennis,Robbins,Liu,Ramsey,Francis,Griffith,Paul,Blair,Oconnor,Cardenas,Pacheco,Cross,Calderon,Quinn,Moss,Swanson,Chan,Rivas,Khan,Rodgers,Serrano,Fitzgerald,Rosales,Stevenson,Christensen,Manning,Gill,Curry,McLaughlin,Harmon,McGee,Gross,Doyle,Garner,Newton,Burgess,Reese,Walton,Blake,Trujillo,Adkins,Brady,Goodman,Roman,Webster,Goodwin,Fischer,Huang,Potter,Delacruz,Montoya,Todd,Wu,Hines,Mullins,Castaneda,Malone,Cannon,Tate,Mack,Sherman,Hubbard,Hodges,Zhang,Guerra,Wolf,Valencia,Saunders,Franco,Rowe,Gallagher,Farmer,Hammond,Hampton,Townsend,Ingram,Wise,Gallegos,Clarke,Barton,Schroeder,Maxwell,Waters,Logan,Camacho,Strickland,Norman,Person,Colon,Parsons,Frank,Harrington,Glover,Osborne,Buchanan,Casey,Floyd,Patton,Ibarra,Ball,Tyler,Suarez,Bowers,Orozco,Salas,Cobb,Gibbs,Andrade,Bauer,Conner,Moody,Escobar,McGuire,Lloyd,Mueller,Hartman,French,Kramer,McBride,Pope,Lindsey,Velazquez,Norton,McCormick,Sparks,Flynn,Yates,Hogan,Marsh,Macias,Villanueva,Zamora,Pratt,Stokes,Owen,Ballard,Lang,Brock,Villarreal,Charles,Drake,Barrera,Cain,Patrick,Pineda,Burnett,Mercado,Santana,Shepherd,Bautista,Ali,Shaffer,Lamb,Trevino,McKenzie,Hess,Beil,Olsen,Cochran,Morton,Nash,Wilkins,Petersen,Briggs,Shah,Roth,Nicholson,Holloway,Lozano,Flowers,Rangel,Hoover,Short,Arias,Mora,Valenzuela,Bryan,Meyers,Weiss,Underwood,Bass,Greer,Summers,Houston,Carson,Morrow,Clayton,Whitaker,Decker,Yoder,Collier,Zuniga,Carey,Wilcox,Melendez,Poole,Roberson,Larsen,Conley,Davenport,Copeland,Massey,Lam,Huff,Rocha,Cameron,Jefferson,Hood,Monroe,Anthony,Pittman,Huynh,Randall,Singleton,Kirk,Combs,Mathis,Christian,Skinner,Bradford,Richard,Galvan,Wall,Boone,Kirby,Wilkinson,Bridges,Bruce,Atkinson,Velez,Meza,Roy,Vincent,York,Hodge,Villa,Abbott,Allison,Tapia,Gates,Chase,Sosa,Sweeney,Farrell,Wyatt,Horn,Dalton,Barron,Phelps,Yu,Dickerson,Heath,Foley,Atkins,Mathews,Bonilla,Acevedo,Benitez,Zavala,Hensley,Glenn,Cisneros,Harrell,Shields,Rubio,Choi,Huffman,Boyer,Garrison,Arroyo,Bond,Kane,Hancock,Callahan,Dillon,Cline,Wiggins,Grimes,Arellano,Melton,Oneill,Savage,Ho,Beltran,Pitts,Parrish,Ponce,Rich,Booth,Koch,Golden,Ware,Brennan,McDowell,Marks,Cantu,Humphrey,Baxter,Sawyer,Clay,Tanner,Gould,Leal,Middleton,Hardin,Hanna,Huerta,Cortez,Hinton,Novak,Magana,Cowan,Whitehead,Preston,Enriquez,Macdonald,Pruitt,Madden,Daugherty,Hess,Nava,Romero,Velasco,Cooke,Roach,Vance,Bullock,Esparza,Schwartz,Cotton,Reardon,Powers,Mahoney,Giles,Duffy,Avery,Albright,Schofield,Blankenship,Gillespie,Moyer,Trejo,Vo,Spangler,Leach,Kerr,Benton,Ambrose,Barnhart,Quintero,Downey,Newell,Rubin,Alston,Sorensen,Burris,Walden,Moser,Fink,Garland,Davila,Mccall,Ewing,Cooley,Vaughan,Bonner,McFarland,Levine,Oneal,Hatfield,Hobbs,Hewitt,Snider,Rosario,Church,Cortes,Enriquez,Mayer,Barajas,Prince,Huber,Frye,Rollins,Browning,Kendall,Landry,Duarte,Merritt,Bloom,Jaramillo,Lester,McKay,Stafford,Odom,Benavides,Herring,Krause,Stout,Hester,Cavazos,Burch,Bentley,Rosen,Meier,Goldstein,McMahon,Waller,Holden,Salgado,Maestas,Johns,Sampson,Corona,Osborn,Petty,Ingle,Sexton,Mays,Rosenthal,Underwood,Coffey,Mccann,Vang,Livingston,Frost,Glass,Aguiar,Tejada,Mulligan,Beasley,Clements,Witt,Ashley,Holman,Donaldson,Chung,Christenson,McDermott,Hickman,Lott,Clay,Finley,Aldridge,Connolly,Kinney,Cuevas,Ramos,Whitley,Bullock,Couch,Faulkner,Donovan,Simms,Calhoun,Jarvis,Cervantes,Humphreys,Crabtree,Gustafson,Rooney,Griggs,Steen,Noel,Vinson,Peacock,Lemus,Colvin,Johns,Abrams,Tilley,Dougherty,Mead,Kraft,Jeffries,Bray,Pennington,Mercer,Bernstein,Arriaga,Humphries,Coulter,Madrid,Weston,Oakes,Pulido,Berger,Greenwood,Quintana,Clary,Buckner,Gallo,Esposito,Brenner,Pino,Villegas,Bueno,Delvalle,Balderas,Felder,Avalos,Franks,Lucero,Covarrubias,Hollins,Centeno,Oneil,Gill,Rushing,Kimball,Harding,Ratliff,Trout,Creech,Carver,Satterfield,Mccullough,Hutchins,Gamboa,Penrod,Posey,Crouch,Parra,Doss,Segura,Carlisle,Gentry,Seymour,Quinones,Bean,Proctor,Marin,Kersey,Guzman,Hamm,Goins,Dukes,Saucedo,Ruff,Heredia,Stanton,Shrader,Mcintosh,Lago,Prieto,Holguin,Hickey,Lantz,Galindo,Collado,Escobedo,Herrera,Griswold,Tobin,Maynard,Sellers,Saldana,Palomino,Nestor,Gaskins,Phan,Kline,Saenz,Mcmillan,Lorenzo,Duong,Fortner,Dang,Vo,Leyva,Peralta,Coronado,Montanez,Paredes,Domingo,Salcedo,Pizarro,Delarosa,Batista,Otero,Fugate,Vigil,Talavera,Guajardo,Segovia,Belcher,Ballesteros,Urbina,Garay,Alcantar,Cavazos,Lamm,Gleason,Zamarripa,Saavedra,Roybal,Malave,Echevarria,Jaimes,Carbajal,Becerra,Tovar,Nieto,Regalado,Roldan,Cardona,Mondragon,Zelaya,Villatoro,Ordonez,Centeno,Delapaz,Naranjo,Tirado,Monarrez,Guardado,Cornejo,Garibay,Andino,Bustillos,Canales,Arreola,Balderas,Barrientos,Briseno,Camarena,Casarez,Castorena,Chairez,Cienfuegos,Covarrubias,Cuellar,Delagarza,Delao,Escamilla,Espinal,Esqueda,Fierro,Galaviz,Gamino,Granados,Guillen,Gurrola,Jaquez,Jurado,Leyva,Limon,Llamas,Loera,Longoria,Loya,Luevano,Madera,Madrigal,Magallanes,Malagon,Manzanares,Mares,Mariscal,Marquez,Mata,Matias,Mayorga,Medrano,Melgar,Menchaca,Mendez,Meraz,Mireles,Mojica,Monreal,Montemayor,Montez,Monzon,Morelos,Murguia,Murrieta,Najar,Najera,Navarrete,Negrete,Nevarez,Noriega,Ocampo,Ochoa,Ojeda,Olivas,Olvera,Ontiveros,Oropeza,Orta,Ozuna,Palafox,Palma,Pantoja,Paramo,Partida,Perales,Perea,Pinon,Plascencia,Polanco,Porras,Portillo,Prado,Puente,Pulido,Quezada,Quiroga,Razo,Renteria,Resendez,Reyna,Rincon,Rios,Robledo,Robles,Rocha,Rodarte,Rojo,Roldan,Romero,Roque,Rosado,Rosales,Rubio,Ruelas,Ruvalcaba,Saavedra,Saenz,Salas,Salazar,Saldana,Saldivar,Salgado,Salinas,Sanabria,Sandoval,Santamaria,Santana,Santiago,Santos,Sauceda,Saucedo,Segovia,Segura,Sepulveda,Serna,Serrano,Sierra,Silva,Solano,Solis,Soliz,Solorio,Soria,Soriano,Sosa,Sotelo,Soto,Suarez,Tabares,Talavera,Tamayo,Tapia,Tejada,Tejeda,Tellez,Terrazas,Tijerina,Tirado,Toledo,Toro,Torres,Tovar,Trejo,Trevino,Trujillo,Uribe,Urquidi,Urrutia,Vaca,Valadez,Valdez,Valencia,Valenzuela,Valles,Vallejo,Varela,Vargas,Vasquez,Vazquez,Vega,Vela,Velasco,Velasquez,Velazquez,Velez,Venegas,Vera,Verduzco,Vergara,Vidal,Vidales,Vigil,Villa,Villagomez,Villalobos,Villalpando,Villanueva,Villareal,Villarreal,Villasenor,Villegas,Yanez,Ybarra,Zambrano,Zamora,Zamudio,Zapata,Zaragoza,Zarate,Zavala,Zepeda,Zuniga';

-- Departments pool
SET @departments = 'Engineering,Human Resources,Finance,Marketing,Sales,Operations,Customer Support,Research & Development,Quality Assurance,Information Technology,Legal,Administration,Product Management,Design,Business Development,Procurement,Logistics,Manufacturing,Maintenance,Security';

-- Designations pool
SET @designations = 'Software Engineer,Senior Software Engineer,Lead Engineer,Engineering Manager,HR Coordinator,HR Manager,HR Director,Financial Analyst,Senior Accountant,Finance Manager,Marketing Specialist,Marketing Manager,Sales Representative,Sales Manager,Operations Analyst,Operations Manager,Support Specialist,Support Manager,Research Scientist,QA Engineer,QA Lead,IT Specialist,System Administrator,Network Engineer,Legal Counsel,Administrative Assistant,Office Manager,Product Manager,Senior Product Manager,UI/UX Designer,Graphic Designer,Business Analyst,Procurement Specialist,Logistics Coordinator,Production Supervisor,Maintenance Technician,Security Officer';

-- Create stored procedure for generating users and employees
DELIMITER //
CREATE PROCEDURE generate_users_and_employees()
BEGIN
    DECLARE company_id INT DEFAULT 1;
    DECLARE emp_count INT;
    DECLARE user_id INT;
    DECLARE emp_id INT;
    DECLARE first_name VARCHAR(50);
    DECLARE last_name VARCHAR(50);
    DECLARE email VARCHAR(255);
    DECLARE emp_code VARCHAR(20);
    DECLARE dept VARCHAR(100);
    DECLARE desig VARCHAR(100);
    DECLARE hire_date DATE;
    DECLARE dob DATE;
    DECLARE gender_val VARCHAR(10);
    DECLARE emp_type VARCHAR(20);
    DECLARE role_id INT;
    DECLARE first_names_count INT DEFAULT 200;
    DECLARE last_names_count INT DEFAULT 500;
    DECLARE dept_count INT DEFAULT 20;
    DECLARE desig_count INT DEFAULT 37;
    
    -- Loop through each company
    WHILE company_id <= 50 DO
        SET emp_count = 1;
        
        -- Generate 50-60 employees per company
        WHILE emp_count <= 50 + FLOOR(RAND() * 11) DO
            -- Generate random names
            SET first_name = SUBSTRING_INDEX(SUBSTRING_INDEX(@first_names, ',', 1 + FLOOR(RAND() * first_names_count)), ',', -1);
            SET last_name = SUBSTRING_INDEX(SUBSTRING_INDEX(@last_names, ',', 1 + FLOOR(RAND() * last_names_count)), ',', -1);
            
            -- Generate email
            SET email = CONCAT(LOWER(first_name), '.', LOWER(last_name), emp_count, '@company', company_id, '.com');
            
            -- Generate employee code
            SET emp_code = CONCAT('EMP', LPAD(company_id, 3, '0'), LPAD(emp_count, 4, '0'));
            
            -- Random department and designation
            SET dept = SUBSTRING_INDEX(SUBSTRING_INDEX(@departments, ',', 1 + FLOOR(RAND() * dept_count)), ',', -1);
            SET desig = SUBSTRING_INDEX(SUBSTRING_INDEX(@designations, ',', 1 + FLOOR(RAND() * desig_count)), ',', -1);
            
            -- Random dates
            SET hire_date = DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND() * 2000) DAY);
            SET dob = DATE_SUB(CURDATE(), INTERVAL (22 + FLOOR(RAND() * 40)) YEAR);
            
            -- Random gender
            SET gender_val = ELT(1 + FLOOR(RAND() * 3), 'male', 'female', 'other');
            
            -- Random employment type
            SET emp_type = ELT(1 + FLOOR(RAND() * 4), 'full_time', 'full_time', 'full_time', 'part_time');
            
            -- Assign role: first 2 employees are Admin and HR, rest are Employee
            IF emp_count = 1 THEN
                SET role_id = 1; -- Admin
            ELSEIF emp_count = 2 THEN
                SET role_id = 2; -- HR
            ELSE
                SET role_id = 3; -- Employee
            END IF;
            
            -- Insert user
            INSERT INTO users (company_id, role_id, email, password_hash, status)
            VALUES (company_id, role_id, email, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');
            
            SET user_id = LAST_INSERT_ID();
            
            -- Insert employee
            INSERT INTO employees (company_id, user_id, employee_code, first_name, last_name, email, phone, date_of_birth, gender, address, hire_date, department, designation, employment_type, status)
            VALUES (
                company_id,
                user_id,
                emp_code,
                first_name,
                last_name,
                email,
                CONCAT('+1-555-', LPAD(FLOOR(RAND() * 10000), 4, '0')),
                dob,
                gender_val,
                CONCAT(FLOOR(100 + RAND() * 9900), ' ', ELT(1 + FLOOR(RAND() * 5), 'Main', 'Oak', 'Maple', 'Cedar', 'Pine'), ' ', ELT(1 + FLOOR(RAND() * 4), 'Street', 'Avenue', 'Boulevard', 'Road')),
                hire_date,
                dept,
                desig,
                emp_type,
                'active'
            );
            
            SET emp_count = emp_count + 1;
        END WHILE;
        
        SET company_id = company_id + 1;
    END WHILE;
END //
DELIMITER ;

-- Execute the procedure
CALL generate_users_and_employees();
DROP PROCEDURE generate_users_and_employees;


-- ============================================================
-- SALARY STRUCTURES (For all employees)
-- ============================================================

DELIMITER //
CREATE PROCEDURE generate_salary_structures()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE emp_id INT;
    DECLARE comp_id INT;
    DECLARE emp_hire DATE;
    DECLARE base_salary DECIMAL(12,2);
    DECLARE housing DECIMAL(12,2);
    DECLARE transport DECIMAL(12,2);
    DECLARE other_allow DECIMAL(12,2);
    DECLARE tax_ded DECIMAL(12,2);
    DECLARE insurance DECIMAL(12,2);
    
    DECLARE emp_cursor CURSOR FOR SELECT id, company_id, hire_date FROM employees;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN emp_cursor;
    
    read_loop: LOOP
        FETCH emp_cursor INTO emp_id, comp_id, emp_hire;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Generate random salary based on some variation
        SET base_salary = 3000 + FLOOR(RAND() * 12000);
        SET housing = base_salary * (0.1 + RAND() * 0.15);
        SET transport = 200 + FLOOR(RAND() * 500);
        SET other_allow = base_salary * (0.05 + RAND() * 0.1);
        SET tax_ded = (base_salary + housing + transport + other_allow) * (0.15 + RAND() * 0.1);
        SET insurance = 150 + FLOOR(RAND() * 350);
        
        INSERT INTO salary_structures (company_id, employee_id, basic_salary, housing_allowance, transport_allowance, other_allowances, tax_deduction, insurance_deduction, other_deductions, effective_date, is_current)
        VALUES (comp_id, emp_id, base_salary, housing, transport, other_allow, tax_ded, insurance, 0, emp_hire, 1);
    END LOOP;
    
    CLOSE emp_cursor;
END //
DELIMITER ;

CALL generate_salary_structures();
DROP PROCEDURE generate_salary_structures;

-- ============================================================
-- ATTENDANCE RECORDS (Last 30 days for all employees)
-- ============================================================

DELIMITER //
CREATE PROCEDURE generate_attendance()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE emp_id INT;
    DECLARE comp_id INT;
    DECLARE day_offset INT;
    DECLARE att_date DATE;
    DECLARE clock_in TIME;
    DECLARE clock_out TIME;
    DECLARE hours_worked DECIMAL(4,2);
    DECLARE att_status VARCHAR(20);
    DECLARE rand_val FLOAT;
    
    DECLARE emp_cursor CURSOR FOR SELECT id, company_id FROM employees WHERE status = 'active';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN emp_cursor;
    
    read_loop: LOOP
        FETCH emp_cursor INTO emp_id, comp_id;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Generate attendance for last 30 days (excluding weekends)
        SET day_offset = 1;
        WHILE day_offset <= 30 DO
            SET att_date = DATE_SUB(CURDATE(), INTERVAL day_offset DAY);
            
            -- Skip weekends
            IF DAYOFWEEK(att_date) NOT IN (1, 7) THEN
                SET rand_val = RAND();
                
                -- 85% present, 5% absent, 5% late, 3% half_day, 2% on_leave
                IF rand_val < 0.85 THEN
                    SET att_status = 'present';
                    SET clock_in = ADDTIME('08:00:00', SEC_TO_TIME(FLOOR(RAND() * 1800)));
                    SET clock_out = ADDTIME('17:00:00', SEC_TO_TIME(FLOOR(RAND() * 3600)));
                    SET hours_worked = TIMESTAMPDIFF(MINUTE, clock_in, clock_out) / 60;
                ELSEIF rand_val < 0.90 THEN
                    SET att_status = 'absent';
                    SET clock_in = NULL;
                    SET clock_out = NULL;
                    SET hours_worked = NULL;
                ELSEIF rand_val < 0.95 THEN
                    SET att_status = 'late';
                    SET clock_in = ADDTIME('09:00:00', SEC_TO_TIME(FLOOR(RAND() * 3600)));
                    SET clock_out = ADDTIME('17:30:00', SEC_TO_TIME(FLOOR(RAND() * 3600)));
                    SET hours_worked = TIMESTAMPDIFF(MINUTE, clock_in, clock_out) / 60;
                ELSEIF rand_val < 0.98 THEN
                    SET att_status = 'half_day';
                    SET clock_in = ADDTIME('08:00:00', SEC_TO_TIME(FLOOR(RAND() * 1800)));
                    SET clock_out = ADDTIME('12:00:00', SEC_TO_TIME(FLOOR(RAND() * 1800)));
                    SET hours_worked = TIMESTAMPDIFF(MINUTE, clock_in, clock_out) / 60;
                ELSE
                    SET att_status = 'on_leave';
                    SET clock_in = NULL;
                    SET clock_out = NULL;
                    SET hours_worked = NULL;
                END IF;
                
                INSERT IGNORE INTO attendance (company_id, employee_id, attendance_date, clock_in_time, clock_out_time, total_hours, status)
                VALUES (comp_id, emp_id, att_date, clock_in, clock_out, hours_worked, att_status);
            END IF;
            
            SET day_offset = day_offset + 1;
        END WHILE;
        
        SET done = FALSE;
    END LOOP;
    
    CLOSE emp_cursor;
END //
DELIMITER ;

CALL generate_attendance();
DROP PROCEDURE generate_attendance;

-- ============================================================
-- LEAVE REQUESTS (Random leave requests)
-- ============================================================

DELIMITER //
CREATE PROCEDURE generate_leave_requests()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE emp_id INT;
    DECLARE comp_id INT;
    DECLARE leave_type INT;
    DECLARE start_dt DATE;
    DECLARE end_dt DATE;
    DECLARE days INT;
    DECLARE req_status VARCHAR(20);
    DECLARE approver INT;
    DECLARE rand_val FLOAT;
    DECLARE leave_count INT;
    
    DECLARE emp_cursor CURSOR FOR SELECT id, company_id FROM employees WHERE status = 'active';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN emp_cursor;
    
    read_loop: LOOP
        FETCH emp_cursor INTO emp_id, comp_id;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Generate 0-3 leave requests per employee
        SET leave_count = FLOOR(RAND() * 4);
        
        WHILE leave_count > 0 DO
            -- Get random leave type for this company
            SELECT id INTO leave_type FROM leave_types WHERE company_id = comp_id ORDER BY RAND() LIMIT 1;
            
            -- Random dates
            SET start_dt = DATE_ADD(CURDATE(), INTERVAL FLOOR(-60 + RAND() * 120) DAY);
            SET days = 1 + FLOOR(RAND() * 5);
            SET end_dt = DATE_ADD(start_dt, INTERVAL days - 1 DAY);
            
            -- Random status
            SET rand_val = RAND();
            IF start_dt < CURDATE() THEN
                IF rand_val < 0.7 THEN
                    SET req_status = 'approved';
                ELSEIF rand_val < 0.85 THEN
                    SET req_status = 'rejected';
                ELSE
                    SET req_status = 'cancelled';
                END IF;
            ELSE
                IF rand_val < 0.4 THEN
                    SET req_status = 'pending';
                ELSEIF rand_val < 0.8 THEN
                    SET req_status = 'approved';
                ELSE
                    SET req_status = 'rejected';
                END IF;
            END IF;
            
            -- Get HR user as approver
            IF req_status IN ('approved', 'rejected') THEN
                SELECT u.id INTO approver FROM users u 
                JOIN employees e ON u.id = e.user_id 
                WHERE u.company_id = comp_id AND u.role_id = 2 
                LIMIT 1;
            ELSE
                SET approver = NULL;
            END IF;
            
            INSERT INTO leave_requests (company_id, employee_id, leave_type_id, start_date, end_date, total_days, reason, status, approver_id, approval_date)
            VALUES (
                comp_id, 
                emp_id, 
                leave_type, 
                start_dt, 
                end_dt, 
                days,
                ELT(1 + FLOOR(RAND() * 5), 'Personal matters', 'Family event', 'Medical appointment', 'Vacation', 'Rest and recovery'),
                req_status,
                approver,
                IF(req_status IN ('approved', 'rejected'), DATE_SUB(start_dt, INTERVAL FLOOR(1 + RAND() * 5) DAY), NULL)
            );
            
            SET leave_count = leave_count - 1;
        END WHILE;
        
        SET done = FALSE;
    END LOOP;
    
    CLOSE emp_cursor;
END //
DELIMITER ;

CALL generate_leave_requests();
DROP PROCEDURE generate_leave_requests;

-- ============================================================
-- PAYROLL RECORDS (Last 3 months)
-- ============================================================

DELIMITER //
CREATE PROCEDURE generate_payroll()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE emp_id INT;
    DECLARE comp_id INT;
    DECLARE sal_id INT;
    DECLARE basic DECIMAL(12,2);
    DECLARE housing DECIMAL(12,2);
    DECLARE transport DECIMAL(12,2);
    DECLARE other_allow DECIMAL(12,2);
    DECLARE tax_ded DECIMAL(12,2);
    DECLARE insurance DECIMAL(12,2);
    DECLARE other_ded DECIMAL(12,2);
    DECLARE gross DECIMAL(12,2);
    DECLARE deductions DECIMAL(12,2);
    DECLARE net DECIMAL(12,2);
    DECLARE pay_year INT;
    DECLARE pay_month INT;
    DECLARE month_offset INT;
    
    DECLARE emp_cursor CURSOR FOR 
        SELECT e.id, e.company_id, s.id, s.basic_salary, s.housing_allowance, s.transport_allowance, s.other_allowances, s.tax_deduction, s.insurance_deduction, s.other_deductions
        FROM employees e
        JOIN salary_structures s ON e.id = s.employee_id AND s.is_current = 1
        WHERE e.status = 'active';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN emp_cursor;
    
    read_loop: LOOP
        FETCH emp_cursor INTO emp_id, comp_id, sal_id, basic, housing, transport, other_allow, tax_ded, insurance, other_ded;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Generate payroll for last 3 months
        SET month_offset = 0;
        WHILE month_offset < 3 DO
            SET pay_year = YEAR(DATE_SUB(CURDATE(), INTERVAL month_offset MONTH));
            SET pay_month = MONTH(DATE_SUB(CURDATE(), INTERVAL month_offset MONTH));
            
            SET gross = basic + housing + transport + other_allow;
            SET deductions = tax_ded + insurance + other_ded;
            SET net = gross - deductions;
            
            INSERT IGNORE INTO payroll_records (company_id, employee_id, salary_structure_id, year, month, gross_salary, total_deductions, net_salary, payment_date, payment_status, payment_reference)
            VALUES (
                comp_id,
                emp_id,
                sal_id,
                pay_year,
                pay_month,
                gross,
                deductions,
                net,
                IF(month_offset > 0, LAST_DAY(DATE_SUB(CURDATE(), INTERVAL month_offset MONTH)), NULL),
                IF(month_offset > 0, 'paid', 'pending'),
                IF(month_offset > 0, CONCAT('PAY-', comp_id, '-', pay_year, LPAD(pay_month, 2, '0'), '-', LPAD(emp_id, 5, '0')), NULL)
            );
            
            SET month_offset = month_offset + 1;
        END WHILE;
        
        SET done = FALSE;
    END LOOP;
    
    CLOSE emp_cursor;
END //
DELIMITER ;

CALL generate_payroll();
DROP PROCEDURE generate_payroll;

-- ============================================================
-- VERIFICATION SUMMARY
-- ============================================================

SELECT 'Data Generation Complete!' AS Status;
SELECT 'Companies' AS Entity, COUNT(*) AS Count FROM companies
UNION ALL SELECT 'Users', COUNT(*) FROM users
UNION ALL SELECT 'Employees', COUNT(*) FROM employees
UNION ALL SELECT 'Leave Types', COUNT(*) FROM leave_types
UNION ALL SELECT 'Attendance Records', COUNT(*) FROM attendance
UNION ALL SELECT 'Leave Requests', COUNT(*) FROM leave_requests
UNION ALL SELECT 'Salary Structures', COUNT(*) FROM salary_structures
UNION ALL SELECT 'Payroll Records', COUNT(*) FROM payroll_records;
