-- DROP TABLES
DROP TABLE DesignerExperienceLevel;
DROP TABLE DesignerDetails;
DROP TABLE DesignerLicense;
DROP TABLE SupervisorDetails;
DROP TABLE Supervisor;
DROP TABLE Contractor;
DROP TABLE ContractorLicense;
DROP TABLE Material;
DROP TABLE Purchase;
DROP TABLE Project;
DROP TABLE ResidentialProject;
DROP TABLE CommercialProject;
DROP TABLE Budget;
DROP TABLE Owner;
DROP TABLE WorkOn;
DROP TABLE WageWorker;
DROP TABLE WageWorkerContractor;
DROP TABLE Review;



-- CREATE TABLES
CREATE TABLE DesignerExperienceLevel ( 
	Designer_Experience_Level VARCHAR(30),
	Designer_Hourly_Rate NUMERIC(4,2) NOT NULL,
	PRIMARY KEY (Designer_Experience_Level)
)


CREATE TABLE DesignerDetails (
	Designer_ID VARCHAR(20),
	Designer_Name VARCHAR(25) NOT NULL,
	Designer_Phone CHAR(10),
    Designer_Specialty VARCHAR(20),
    Supervisor_ID VARCHAR(20),
    PRIMARY KEY (Designer_ID,Designer_Phone),
    FOREIGN KEY (Supervisor_ID) REFERENCES Supervisor
)


CREATE TABLE DesignerLicense (
    Designer_License_Number VARCHAR(20),
    Designer_ID VARCHAR(20) NOT NULL,
    PRIMARY KEY (Designer_License_Number)
)


CREATE TABLE SupervisorDetails(
	Supervisor_Name VARCHAR(25) NOT NULL,
	Supervisor_Phone CHAR(10),
	Designer_Experience_Level VARCHAR(30) NOT NULL,
	Designer_License_Number VARCHAR(20) NOT NULL,
    PRIMARY KEY (Supervisor_Phone),
	FOREIGN KEY (Designer_License_Number) REFERENCES  
        DesignerLicense,
	FOREIGN KEY (Designer_Experience_Level) REFERENCES 
        DesignerExperienceLevel
)


CREATE TABLE Supervisor (
    Supervisor_ID VARCHAR(20),
    Supervisor_Name VARCHAR(25) NOT NULL,
    Supervisor_Phone CHAR(10),
    PRIMARY KEY (Supervisor_ID,Supervisor_Phone)
)


CREATE TABLE Contractor (
    Contractor_ID VARCHAR(20),
    Contractor_Name VARCHAR(25) NOT NULL,
    Contractor_Specialization VARCHAR(20),
    Contractor_Phone CHAR(10),
    Supervisor_ID VARCHAR(20),
    PRIMARY KEY (Contractor_ID,Contractor_Phone),
    FOREIGN KEY (Supervisor_ID) REFERENCES Supervisor
)


CREATE TABLE ContractorLicense (
    Contractor_License_Number VARCHAR(20),
    Supervisor_Name VARCHAR(25) NOT NULL,
    Supervisor_Phone CHAR(10),
    Contractor_ID VARCHAR(20) NOT NULL,
    PRIMARY KEY (Contractor_License_Number, Supervisor_Phone),
    FOREIGN KEY (Contractor_ID) REFERENCES Contractor
)



CREATE TABLE Material (
    Material_ID VARCHAR(20),
    Material_Type VARCHAR(25),
    Material_Name VARCHAR(25) NOT NULL,
    Material_Order_Quantity VARCHAR(25),
    Material_Used_Quantity VARCHAR(25),
    Material_Order_Date DATE,
    Material_Cost_Per_Unit NUMERIC(5,2),
    PRIMARY KEY (Material_ID)
)

CREATE TABLE Purchase (
    Material_ID VARCHAR(20),
    Contractor_ID VARCHAR(20),
    PRIMARY KEY (Material_ID,Contractor_ID),
    FOREIGN KEY (Material_ID) REFERENCES Material,
    FOREIGN KEY (Contractor_ID) REFERENCES Contractor
)


CREATE TABLE Project (
    Project_ID VARCHAR(20),
    Project_Address VARCHAR(50),
    Project_Name VARCHAR(25) NOT NULL,
    Project_Start_Date DATE,
    Project_End_Date DATE,
    Project_Status VARCHAR(20),
    Supervisor_ID VARCHAR(20) NOT NULL,
    Budget_ID VARCHAR(20) NOT NULL,
    Owner_ID VARCHAR(20) NOT NULL,	
    PRIMARY KEY (Project_ID),
    FOREIGN KEY (Supervisor_ID) REFERENCES Supervisor,
    FOREIGN KEY (Budget_ID) REFERENCES Budget,
    FOREIGN KEY (Owner_ID) REFERENCES Owner

)

CREATE TABLE ResidentialProject (
	Project_ID VARCHAR(20),
	Property_Type VARCHAR(20),
	No_of_rooms_To_Renovate INTEGER,
	PRIMARY KEY (Project_ID),
	FOREIGN KEY (Project_ID) REFERENCES Project
)

CREATE TABLE CommercialProject (
	Project_ID VARCHAR(20),
	Business_Type VARCHAR(20),
	PRIMARY KEY (Project_ID),
	FOREIGN KEY (Project_ID) REFERENCES Project
)


CREATE TABLE Budget (
	Budget_ID VARCHAR(20),
	Budget_Material_Cost NUMERIC (10,2),
	Budget_Initial_Estimate NUMERIC (10,2) NOT NULL,
    Budget_Contractor_Fees NUMERIC(10,2),
    Budget_Total_Cost NUMERIC(10,2),
    Budget_Wage_Worker_Cost NUMERIC(10,2),
    PRIMARY KEY (Budget_ID)
)

CREATE TABLE Owner (
	Owner_ID VARCHAR(20),
	Owner_Name VARCHAR(25) NOT NULL,
	Owner_Type VARCHAR(20),
	Owner_Phone CHAR(10),
	PRIMARY KEY (Owner_ID, Owner_Phone)
)

CREATE TABLE WorkOn (
    Contractor_ID VARCHAR(20),  
    Project_ID VARCHAR(20),  
   	PRIMARY KEY (Contractor_ID, Project_ID),
   	FOREIGN KEY (Contractor_ID) REFERENCES Contractor ON DELETE CASCADE,
   	FOREIGN KEY (Project_ID) REFERENCES Project ON DELETE CASCADE
)

CREATE TABLE WageWorker (
	Wage_Worker_ID VARCHAR(20),
    Contractor_ID VARCHAR(20),
   	Wage_Worker_Hourly_Rate NUMERIC(4, 2) NOT NULL,
   	Wage_Worker_Skills VARCHAR(20),
   	PRIMARY KEY (Wage_Worker_ID,Contractor_ID),
   	FOREIGN KEY (Contractor_ID) REFERENCES Contractor, ON DELETE CASCADE
)

CREATE TABLE  WageWorkerContractor (
	Contractor_License_Number VARCHAR(20),
	Wage_Worker_ID VARCHAR(20),
	Contractor_ID VARCHAR(20),
	PRIMARY KEY (Contractor_License_Number),
	FOREIGN KEY (Wage_Worker_ID) REFERENCES WageWorker
)


CREATE TABLE Review (
    Review_ID VARCHAR(20) NOT NULL,
    Review_Date DATE,
    Review_Rating CHAR(1),
    Review_Comment VARCHAR(100),
    Owner_ID VARCHAR(20) NOT NULL,
    PRIMARY KEY (Review_ID),
    FOREIGN KEY (Owner_ID) REFERENCES Owner

)



-- INSERT TUPLES FROM MILESTONE - 2
-- Tuples for DesignerExperienceLevel
INSERT INTO DesignerExperienceLevel(Designer_Experience_Level, Designer_Hourly_Rate) VALUES ('Junior', 30.00);
INSERT INTO DesignerExperienceLevel(Designer_Experience_Level, Designer_Hourly_Rate) VALUES ('Intermediate',40.50); 
INSERT INTO DesignerExperienceLevel(Designer_Experience_Level, Designer_Hourly_Rate) VALUES ('Senior', 50.00);
INSERT INTO DesignerExperienceLevel(Designer_Experience_Level, Designer_Hourly_Rate) VALUES ('Lead',65.00);
INSERT INTO DesignerExperienceLevel(Designer_Experience_Level, Designer_Hourly_Rate) VALUES ('Junior',30.00);


-- Tuples for DesignerDetails
INSERT INTO DesignerDetails (Designer_ID, Designer_Name, Designer_Phone, Designer_Specialty, Supervisor_ID) VALUES ('D001', 'Alice Johnson', '1234567890', 'Interior Design', 'S001');
INSERT INTO DesignerDetails (Designer_ID, Designer_Name, Designer_Phone, Designer_Specialty, Supervisor_ID) VALUES ('D002', 'Bob Smith', '2345678901', 'Landscape Design', 'S002');
INSERT INTO DesignerDetails (Designer_ID, Designer_Name, Designer_Phone, Designer_Specialty, Supervisor_ID) VALUES ('D003', 'Cathy T', '3456789012', NULL, 'S003');
INSERT INTO DesignerDetails (Designer_ID, Designer_Name, Designer_Phone, Designer_Specialty, Supervisor_ID) VALUES ('D004', 'David Brown', '4567890123', 'Sustainable Design', 'S001');
INSERT INTO DesignerDetails (Designer_ID, Designer_Name, Designer_Phone, Designer_Specialty, Supervisor_ID) VALUES ('D005', 'Eva Brown', '5678901234', 'Urban Design', 'S004');


-- Tuples for DesignerLicense
INSERT INTO DesignerLicense (Designer_License_Number, Designer_ID) VALUES ('DL001', 'D001');
INSERT INTO DesignerLicense (Designer_License_Number, Designer_ID) VALUES ('DL002', 'D002');
INSERT INTO DesignerLicense (Designer_License_Number, Designer_ID) VALUES ('DL003', 'D003');
INSERT INTO DesignerLicense (Designer_License_Number, Designer_ID) VALUES ('DL004', 'D004');
INSERT INTO DesignerLicense (Designer_License_Number, Designer_ID) VALUES ('DL005', 'D005');

-- Tuples for SupervisorDetails
INSERT INTO SupervisorDetails (Supervisor_Name, Supervisor_Phone, Designer_Experience_Level, Designer_License_Number) VALUES ('Michael Lee', '6789012345', 'Senior', 'DL001');
INSERT INTO SupervisorDetails (Supervisor_Name, Supervisor_Phone, Designer_Experience_Level, Designer_License_Number) VALUES ('Sarah Miller', '7890123456', 'Lead', 'DL002');
INSERT INTO SupervisorDetails (Supervisor_Name, Supervisor_Phone, Designer_Experience_Level, Designer_License_Number) VALUES ('James Wilson', '8901234567', 'Junior', 'DL003');
INSERT INTO SupervisorDetails (Supervisor_Name, Supervisor_Phone, Designer_Experience_Level, Designer_License_Number) VALUES ('Nancy White', '9012345678', 'Intermediate', 'DL004');
INSERT INTO SupervisorDetails (Supervisor_Name, Supervisor_Phone, Designer_Experience_Level, Designer_License_Number) VALUES ('Tom Harris', '0123456789', 'Junior', 'DL005');


-- Tuples for Supervisor
INSERT INTO Supervisor (Supervisor_ID, Supervisor_Name, Supervisor_Phone) VALUES ('S001', 'Michael Lee', '6789012345');
INSERT INTO Supervisor (Supervisor_ID, Supervisor_Name, Supervisor_Phone) VALUES ('S002', 'Sarah Miller', '7890123456');
INSERT INTO Supervisor (Supervisor_ID, Supervisor_Name, Supervisor_Phone) VALUES ('S003', 'James Wilson', '8901234567');
INSERT INTO Supervisor (Supervisor_ID, Supervisor_Name, Supervisor_Phone) VALUES ('S004', 'Nancy White', '9012345678');
INSERT INTO Supervisor (Supervisor_ID, Supervisor_Name, Supervisor_Phone) VALUES ('S005', 'Tom Harris', '0123456789');


-- Tuples for Contractor
INSERT INTO Contractor (Contractor_ID, Contractor_Name, Contractor_Specialization, Contractor_Phone, Supervisor_ID) VALUES ('C001', 'X Construction', 'Lighting', '1122334455', 'S001');
INSERT INTO Contractor (Contractor_ID, Contractor_Name, Contractor_Specialization, Contractor_Phone, Supervisor_ID) VALUES ('C002', 'Y Builders', 'Plumbing', '2233445566', 'S002');
INSERT INTO Contractor (Contractor_ID, Contractor_Name, Contractor_Specialization, Contractor_Phone, Supervisor_ID) VALUES ('C003', 'Z Developers', 'Masonry', '3344556677', 'S003');
INSERT INTO Contractor (Contractor_ID, Contractor_Name, Contractor_Specialization, Contractor_Phone, Supervisor_ID) VALUES ('C004', 'Alpha Construction', 'Electrician', '4455667788', 'S004');
INSERT INTO Contractor (Contractor_ID, Contractor_Name, Contractor_Specialization, Contractor_Phone, Supervisor_ID) VALUES ('C005', 'Beta Builders', 'Mixed-use', '5566778899', 'S005');


-- Tuples for ContractorLicense
INSERT INTO ContractorLicense (Contractor_License_Number, Supervisor_Name, Supervisor_Phone, Contractor_ID) VALUES ('CL001', 'Michael Lee', '6789012345', 'C001');
INSERT INTO ContractorLicense (Contractor_License_Number, Supervisor_Name, Supervisor_Phone, Contractor_ID) VALUES ('CL002', 'Sarah Miller', '7890123456', 'C002');
INSERT INTO ContractorLicense (Contractor_License_Number, Supervisor_Name, Supervisor_Phone, Contractor_ID) VALUES ('CL003', 'James Wilson', '8901234567', 'C003');
INSERT INTO ContractorLicense (Contractor_License_Number, Supervisor_Name, Supervisor_Phone, Contractor_ID) VALUES ('CL004', 'Nancy White', '9012345678', 'C004');
INSERT INTO ContractorLicense (Contractor_License_Number, Supervisor_Name, Supervisor_Phone, Contractor_ID) VALUES ('CL005', 'Tom Harris', '0123456789', 'C005');


-- Tuples for Material
INSERT INTO Material (Material_ID, Material_Type, Material_Name, Material_Order_Quantity, Material_Used_Quantity, Material_Order_Date, Material_Cost_Per_Unit) VALUES ('M001', 'Wood', 'Plywood', '100', '75', '2023-01-15', 12.50);
INSERT INTO Material (Material_ID, Material_Type, Material_Name, Material_Order_Quantity, Material_Used_Quantity, Material_Order_Date, Material_Cost_Per_Unit) VALUES ('M002', 'Concrete', 'Cement', '50', '30', '2023-02-10', 8.25);
INSERT INTO Material (Material_ID, Material_Type, Material_Name, Material_Order_Quantity, Material_Used_Quantity, Material_Order_Date, Material_Cost_Per_Unit) VALUES ('M003', 'Steel', 'Rebar', '200', '150', '2023-03-05', 15.00);
INSERT INTO Material (Material_ID, Material_Type, Material_Name, Material_Order_Quantity, Material_Used_Quantity, Material_Order_Date, Material_Cost_Per_Unit) VALUES ('M004', 'Glass', 'Window Glass', '70', '50', '2023-04-20', 22.30);
INSERT INTO Material (Material_ID, Material_Type, Material_Name, Material_Order_Quantity, Material_Used_Quantity, Material_Order_Date, Material_Cost_Per_Unit) VALUES ('M005', 'Plastic', 'PVC Pipes', '120', '100', '2023-05-15', 3.75);



-- Tuples for Purchase
INSERT INTO Purchase (Material_ID, Contractor_ID) VALUES ('M001', 'C001');
INSERT INTO Purchase (Material_ID, Contractor_ID) VALUES ('M002', 'C002');
INSERT INTO Purchase (Material_ID, Contractor_ID) VALUES ('M003', 'C003');
INSERT INTO Purchase (Material_ID, Contractor_ID) VALUES ('M004', 'C004');
INSERT INTO Purchase (Material_ID, Contractor_ID) VALUES ('M005', 'C005');


-- Tuples for Project
INSERT INTO Project (Project_ID, Project_Address, Project_Name, Project_Start_Date, Project_End_Date, Project_Status, Supervisor_ID, Budget_ID, Owner_ID) VALUES ('P001', '123 Main St', 'Residential Project', '2023-01-10', '2023-12-15', 'In Progress', 'S001', 'B001', 'O001');
INSERT INTO Project (Project_ID, Project_Address, Project_Name, Project_Start_Date, Project_End_Date, Project_Status, Supervisor_ID, Budget_ID, Owner_ID) VALUES ('P002', '456 Market St', 'Commercial Complex', '2023-02-20', '2024-01-20', 'In Progress', 'S002', 'B002', 'O002');
INSERT INTO Project (Project_ID, Project_Address, Project_Name, Project_Start_Date, Project_End_Date, Project_Status, Supervisor_ID, Budget_ID, Owner_ID) VALUES ('P003', '789 Pine St', 'Urban Renewal', '2023-03-15', '2024-06-15', 'Not Started', 'S003', 'B003', 'O003');
INSERT INTO Project (Project_ID, Project_Address, Project_Name, Project_Start_Date, Project_End_Date, Project_Status, Supervisor_ID, Budget_ID, Owner_ID) VALUES ('P004', '1010 Oak St', 'Sustainable Housing', '2023-04-25', '2024-09-10', 'Completed', 'S004', 'B004', 'O004');
INSERT INTO Project (Project_ID, Project_Address, Project_Name, Project_Start_Date, Project_End_Date, Project_Status, Supervisor_ID, Budget_ID, Owner_ID) VALUES ('P005', '202 Elm St', 'Mixed-Use Development', '2023-05-05', '2024-03-01', 'In Progress', 'S005', 'B005', 'O005');


-- Tuples for Residential Project
INSERT INTO ResidentialProject (Project_ID, Property_Type, No_of_rooms_To_Renovate) VALUES ('P001', 'Single Family', 3);
INSERT INTO ResidentialProject (Project_ID, Property_Type, No_of_rooms_To_Renovate) VALUES ('P004', 'Villa', 5);
INSERT INTO ResidentialProject (Project_ID, Property_Type, No_of_rooms_To_Renovate) VALUES ('P006', 'Condo', 2);
INSERT INTO ResidentialProject (Project_ID, Property_Type, No_of_rooms_To_Renovate) VALUES ('P007', 'Townhouse', 4);
INSERT INTO ResidentialProject (Project_ID, Property_Type, No_of_rooms_To_Renovate) VALUES ('P008', 'Luxury Villa', 7);


-- Tuples for CommercialProject
INSERT INTO CommercialProject (Project_ID, Business_Type) VALUES ('P002', 'Shopping Mall');
INSERT INTO CommercialProject (Project_ID, Business_Type) VALUES ('P003', 'Office Complex');
INSERT INTO CommercialProject (Project_ID, Business_Type) VALUES ('P009', 'Warehouse');
INSERT INTO CommercialProject (Project_ID, Business_Type) VALUES ('P010', 'Hotel');
INSERT INTO CommercialProject (Project_ID, Business_Type) VALUES ('P011', 'Retail Store');


-- Tuples for Budget
INSERT INTO Budget (Budget_ID, Budget_Material_Cost, Budget_Initial_Estimate, Budget_Contractor_Fees, Budget_Total_Cost, Budget_Wage_Worker_Cost) VALUES ('B001', 50000.00, 60000.00, 10000.00, 70000.00, 5000.00);
INSERT INTO Budget (Budget_ID, Budget_Material_Cost, Budget_Initial_Estimate, Budget_Contractor_Fees, Budget_Total_Cost, Budget_Wage_Worker_Cost) VALUES ('B002', 80000.00, 90000.00, 15000.00, 105000.00, 10000.00);
INSERT INTO Budget (Budget_ID, Budget_Material_Cost, Budget_Initial_Estimate, Budget_Contractor_Fees, Budget_Total_Cost, Budget_Wage_Worker_Cost) VALUES ('B003', 100000.00, 110000.00, 20000.00, 130000.00, 15000.00);
INSERT INTO Budget (Budget_ID, Budget_Material_Cost, Budget_Initial_Estimate, Budget_Contractor_Fees, Budget_Total_Cost, Budget_Wage_Worker_Cost) VALUES ('B004', 120000.00, 130000.00, 25000.00, 155000.00, 20000.00);
INSERT INTO Budget (Budget_ID, Budget_Material_Cost, Budget_Initial_Estimate, Budget_Contractor_Fees, Budget_Total_Cost, Budget_Wage_Worker_Cost) VALUES ('B005', 90000.00, 100000.00, 18000.00, 118000.00, 12000.00);



-- Tuples for Owner
INSERT INTO Owner (Owner_ID, Owner_Name, Owner_Type, Owner_Phone) VALUES ('O001', 'John Doe', 'Individual', '9876543210');
INSERT INTO Owner (Owner_ID, Owner_Name, Owner_Type, Owner_Phone) VALUES ('O002', 'Jane Smith', 'Company', '8765432109');
INSERT INTO Owner (Owner_ID, Owner_Name, Owner_Type, Owner_Phone) VALUES ('O003', 'Green Solutions', 'Non-Profit', '7654321098');
INSERT INTO Owner (Owner_ID, Owner_Name, Owner_Type, Owner_Phone) VALUES ('O004', 'Eco Builders', 'Company', '6543210987');
INSERT INTO Owner (Owner_ID, Owner_Name, Owner_Type, Owner_Phone) VALUES ('O005', 'Sustainable Living', 'Non-Profit', '5432109876');


-- Tuples for WorkOn
INSERT INTO WorkOn (Contractor_ID, Project_ID) VALUES ('C001', 'P001');
INSERT INTO WorkOn (Contractor_ID, Project_ID) VALUES ('C002', 'P002');
INSERT INTO WorkOn (Contractor_ID, Project_ID) VALUES ('C003', 'P003');
INSERT INTO WorkOn (Contractor_ID, Project_ID) VALUES ('C004', 'P004');
INSERT INTO WorkOn (Contractor_ID, Project_ID) VALUES ('C005', 'P005');


-- Tuples for WageWoker
INSERT INTO WageWorker (Wage_Worker_ID, Contractor_ID, Wage_Worker_Hourly_Rate, Wage_Worker_Skills) VALUES ('WW001', 'C001', 25.00, 'Carpentry');
INSERT INTO WageWorker (Wage_Worker_ID, Contractor_ID, Wage_Worker_Hourly_Rate, Wage_Worker_Skills) VALUES ('WW002', 'C002', 30.00, 'Plumbing');
INSERT INTO WageWorker (Wage_Worker_ID, Contractor_ID, Wage_Worker_Hourly_Rate, Wage_Worker_Skills) VALUES ('WW003', 'C003', 35.00, 'Electrical');
INSERT INTO WageWorker (Wage_Worker_ID, Contractor_ID, Wage_Worker_Hourly_Rate, Wage_Worker_Skills) VALUES ('WW004', 'C004', 28.00, 'Masonry');
INSERT INTO WageWorker (Wage_Worker_ID, Contractor_ID, Wage_Worker_Hourly_Rate, Wage_Worker_Skills) VALUES ('WW005', 'C005', 22.00, 'Painting');

-- Tuples for WageWorkerContractor
INSERT INTO WageWorkerContractor (Contractor_License_Number, Wage_Worker_ID, Contractor_ID) VALUES ('CL001', 'WW001', 'C001');
INSERT INTO WageWorkerContractor (Contractor_License_Number, Wage_Worker_ID, Contractor_ID) VALUES ('CL002', 'WW002', 'C002');
INSERT INTO WageWorkerContractor (Contractor_License_Number, Wage_Worker_ID, Contractor_ID) VALUES ('CL003', 'WW003', 'C003');
INSERT INTO WageWorkerContractor (Contractor_License_Number, Wage_Worker_ID, Contractor_ID) VALUES ('CL004', 'WW004', 'C004');
INSERT INTO WageWorkerContractor (Contractor_License_Number, Wage_Worker_ID, Contractor_ID) VALUES ('CL005', 'WW005', 'C005');

-- Tuples for Review
INSERT INTO Review (Review_ID, Review_Date, Review_Rating, Review_Comment, Owner_ID) VALUES ('R001', '2023-01-15', '5', 'Great service, highly recommended', 'O001');
INSERT INTO Review (Review_ID, Review_Date, Review_Rating, Review_Comment, Owner_ID) VALUES ('R002', '2023-02-20', '4', 'Satisfactory work, but some delays.', 'O002');
INSERT INTO Review (Review_ID, Review_Date, Review_Rating, Review_Comment, Owner_ID) VALUES ('R003', '2023-03-05', '5', 'Excellent quality and timely delivery.', 'O003');
INSERT INTO Review (Review_ID, Review_Date, Review_Rating, Review_Comment, Owner_ID) VALUES ('R004', '2023-04-10', '2', 'Average experience, some issues with communication.', 'O004');
INSERT INTO Review (Review_ID, Review_Date, Review_Rating, Review_Comment, Owner_ID) VALUES ('R005', '2023-05-25', '3', 'Good work, but room for improvement.', 'O005');



