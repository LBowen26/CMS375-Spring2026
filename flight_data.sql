DROP DATABASE IF EXISTS OrlandoAirportDB;
CREATE DATABASE OrlandoAirportDB;
USE OrlandoAirportDB;


DROP TABLE IF EXISTS TicketPrices;
DROP TABLE IF EXISTS Flights;
DROP TABLE IF EXISTS Airports;
DROP TABLE IF EXISTS Airlines;

CREATE TABLE Airlines (
    airline_id INT PRIMARY KEY,
    airline_name VARCHAR(50) NOT NULL
);

CREATE TABLE Airports (
    airport_id INT PRIMARY KEY,
    airport_code VARCHAR(10) NOT NULL,
    airport_name VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    state VARCHAR(50) NOT NULL
);

CREATE TABLE Flights (
    flight_id INT PRIMARY KEY,
    airline_id INT NOT NULL,
    departure_airport_id INT NOT NULL,
    arrival_airport_id INT NOT NULL,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    flight_duration INT NOT NULL,
    gate_number VARCHAR(10),
    terminal VARCHAR(10),
    aircraft_type VARCHAR(50),
    is_delayed boolean,	
    passenger_count INT,
    seats_available BOOLEAN,
    FOREIGN KEY (airline_id) REFERENCES Airlines(airline_id),
    FOREIGN KEY (departure_airport_id) REFERENCES Airports(airport_id),
    FOREIGN KEY (arrival_airport_id) REFERENCES Airports(airport_id)
);

CREATE TABLE TicketPrices (
    price_id INT PRIMARY KEY,
    flight_id INT NOT NULL,
    section_name VARCHAR(20) NOT NULL,
    ticket_price DECIMAL(8,2) NOT NULL,
    FOREIGN KEY (flight_id) REFERENCES Flights(flight_id)
);


-- AIRLINES


INSERT INTO Airlines VALUES
(1, 'Delta Airlines'),
(2, 'American Airlines'),
(3, 'United Airlines'),
(4, 'Southwest Airlines'),
(5, 'JetBlue Airways'),
(6, 'Spirit Airlines'),
(7, 'Frontier Airlines'),
(8, 'Alaska Airlines');


-- AIRPORTS


INSERT INTO Airports VALUES
(1, 'MCO', 'Orlando International Airport', 'Orlando', 'Florida'),
(2, 'ATL', 'Hartsfield-Jackson Atlanta International Airport', 'Atlanta', 'Georgia'),
(3, 'JFK', 'John F. Kennedy International Airport', 'New York', 'New York'),
(4, 'ORD', 'Chicago O''Hare International Airport', 'Chicago', 'Illinois'),
(5, 'HOU', 'William P. Hobby Airport', 'Houston', 'Texas'),
(6, 'BOS', 'Logan International Airport', 'Boston', 'Massachusetts'),
(7, 'LAS', 'Harry Reid International Airport', 'Las Vegas', 'Nevada'),
(8, 'DEN', 'Denver International Airport', 'Denver', 'Colorado'),
(9, 'SEA', 'Seattle-Tacoma International Airport', 'Seattle', 'Washington'),
(10, 'LAX', 'Los Angeles International Airport', 'Los Angeles', 'California'),
(11, 'DFW', 'Dallas/Fort Worth International Airport', 'Dallas', 'Texas'),
(12, 'SFO', 'San Francisco International Airport', 'San Francisco', 'California'),
(13, 'SJU', 'Luis Muñoz Marín International Airport', 'San Juan', 'Puerto Rico'),
(14, 'BNA', 'Nashville International Airport', 'Nashville', 'Tennessee'),
(15, 'DTW', 'Detroit Metropolitan Airport', 'Detroit', 'Michigan'),
(16, 'PHL', 'Philadelphia International Airport', 'Philadelphia', 'Pennsylvania'),
(17, 'MIA', 'Miami International Airport', 'Miami', 'Florida'),
(18, 'CLT', 'Charlotte Douglas International Airport', 'Charlotte', 'North Carolina'),
(19, 'IAD', 'Washington Dulles International Airport', 'Washington', 'Virginia'),
(20, 'STL', 'St. Louis Lambert International Airport', 'St. Louis', 'Missouri'),
(21, 'EWR', 'Newark Liberty International Airport', 'Newark', 'New Jersey'),
(22, 'CLE', 'Cleveland Hopkins International Airport', 'Cleveland', 'Ohio'),
(23, 'PHX', 'Phoenix Sky Harbor International Airport', 'Phoenix', 'Arizona'),
(24, 'PDX', 'Portland International Airport', 'Portland', 'Oregon'),
(25, 'MSP', 'Minneapolis-Saint Paul International Airport', 'Minneapolis', 'Minnesota'),
(26, 'CUN', 'Cancún International Airport', 'Cancún', 'Mexico'),
(27, 'BWI', 'Baltimore/Washington International Airport', 'Baltimore', 'Maryland'),
(28, 'FLL', 'Fort Lauderdale-Hollywood International Airport', 'Fort Lauderdale', 'Florida'),
(29, 'TTN', 'Trenton-Mercer Airport', 'Trenton', 'New Jersey');


-- FLIGHTS


INSERT INTO Flights VALUES
(1, 1, 1, 2, '2026-04-01 08:00:00', '2026-04-01 09:30:00', 90, 'A12', 'A', 'Boeing 737-800', FALSE, 142, TRUE),
(2, 2, 1, 3, '2026-04-01 07:15:00', '2026-04-01 10:00:00', 165, 'B7', 'B', 'Airbus A321', TRUE, 185, TRUE),
(3, 3, 1, 4, '2026-04-01 09:45:00', '2026-04-01 12:15:00', 150, 'C3', 'C', 'Boeing 737 MAX 8', FALSE, 176, FALSE),
(4, 4, 1, 5, '2026-04-01 06:30:00', '2026-04-01 08:45:00', 135, 'A5', 'A', 'Boeing 737-700', FALSE, 138, TRUE),
(5, 5, 1, 6, '2026-04-01 11:00:00', '2026-04-01 14:00:00', 180, 'B11', 'B', 'Airbus A220-300', TRUE, 154, TRUE),
(6, 6, 1, 7, '2026-04-01 13:20:00', '2026-04-01 16:30:00', 250, 'C8', 'C', 'Airbus A320neo', FALSE, 181, TRUE),
(7, 7, 1, 8, '2026-04-01 15:10:00', '2026-04-01 18:30:00', 260, 'A18', 'A', 'Airbus A321neo', TRUE, 190, FALSE),
(8, 8, 1, 9, '2026-04-01 10:30:00', '2026-04-01 14:45:00', 315, 'B14', 'B', 'Boeing 737-900ER', FALSE, 168, TRUE),
(9, 1, 1, 10, '2026-04-01 17:00:00', '2026-04-01 20:15:00', 315, 'C10', 'C', 'Boeing 757-200', TRUE, 199, TRUE),
(10, 2, 1, 11, '2026-04-01 18:45:00', '2026-04-01 21:00:00', 135, 'A9', 'A', 'Airbus A319', FALSE, 126, FALSE),
(11, 3, 1, 12, '2026-04-01 19:30:00', '2026-04-01 23:45:00', 315, 'B2', 'B', 'Boeing 737 MAX 9', TRUE, 172, TRUE),
(12, 5, 1, 13, '2026-04-01 12:50:00', '2026-04-01 15:30:00', 160, 'C6', 'C', 'Airbus A321', FALSE, 161, TRUE),
(13, 4, 1, 14, '2026-04-01 14:00:00', '2026-04-01 15:45:00', 105, 'A3', 'A', 'Boeing 737-800', FALSE, 143, TRUE),
(14, 6, 1, 15, '2026-04-01 16:25:00', '2026-04-01 19:00:00', 155, 'B13', 'B', 'Airbus A320', TRUE, 178, FALSE),
(15, 7, 1, 16, '2026-04-01 20:10:00', '2026-04-01 22:45:00', 155, 'C1', 'C', 'Airbus A320neo', FALSE, 169, TRUE),

(16, 1, 1, 17, '2026-04-02 06:50:00', '2026-04-02 08:00:00', 70, 'A10', 'A', 'Boeing 717', FALSE, 117, TRUE),
(17, 2, 1, 18, '2026-04-02 08:20:00', '2026-04-02 10:05:00', 105, 'B4', 'B', 'Airbus A320', FALSE, 149, TRUE),
(18, 3, 1, 19, '2026-04-02 09:10:00', '2026-04-02 11:25:00', 135, 'C4', 'C', 'Airbus A319', TRUE, 133, TRUE),
(19, 4, 1, 20, '2026-04-02 10:45:00', '2026-04-02 13:05:00', 140, 'A7', 'A', 'Boeing 737-700', FALSE, 144, FALSE),
(20, 5, 1, 21, '2026-04-02 11:25:00', '2026-04-02 14:05:00', 160, 'B15', 'B', 'Airbus A321neo', TRUE, 177, TRUE),
(21, 6, 1, 22, '2026-04-02 12:40:00', '2026-04-02 15:10:00', 150, 'C9', 'C', 'Airbus A320', FALSE, 165, TRUE),
(22, 7, 1, 23, '2026-04-02 13:15:00', '2026-04-02 16:45:00', 270, 'A16', 'A', 'Airbus A321neo', TRUE, 186, TRUE),
(23, 8, 1, 24, '2026-04-02 14:05:00', '2026-04-02 18:20:00', 315, 'B8', 'B', 'Boeing 737-800', FALSE, 171, FALSE),
(24, 1, 1, 25, '2026-04-02 15:00:00', '2026-04-02 18:05:00', 185, 'C11', 'C', 'Airbus A321', FALSE, 158, TRUE),
(25, 2, 1, 16, '2026-04-02 16:30:00', '2026-04-02 19:05:00', 155, 'A14', 'A', 'Boeing 737-800', TRUE, 162, TRUE),
(26, 3, 1, 8, '2026-04-02 17:20:00', '2026-04-02 20:05:00', 165, 'B1', 'B', 'Boeing 737 MAX 8', FALSE, 180, FALSE),
(27, 5, 1, 26, '2026-04-02 18:10:00', '2026-04-02 20:00:00', 110, 'C12', 'C', 'Airbus A320', TRUE, 151, TRUE),
(28, 4, 1, 27, '2026-04-02 19:00:00', '2026-04-02 21:20:00', 140, 'A6', 'A', 'Boeing 737-800', FALSE, 147, TRUE),
(29, 6, 1, 28, '2026-04-02 20:25:00', '2026-04-02 21:25:00', 60, 'B16', 'B', 'Airbus A319', FALSE, 119, TRUE),
(30, 7, 1, 29, '2026-04-02 21:10:00', '2026-04-02 23:45:00', 155, 'C2', 'C', 'Airbus A320neo', TRUE, 174, FALSE);


-- TICKET PRICES


INSERT INTO TicketPrices VALUES
(1, 1, 'Economy', 120.00), (2, 1, 'Business', 320.00), (3, 1, 'First', 550.00),
(4, 2, 'Economy', 180.00), (5, 2, 'Business', 450.00), (6, 2, 'First', 800.00),
(7, 3, 'Economy', 160.00), (8, 3, 'Business', 420.00), (9, 3, 'First', 750.00),
(10, 4, 'Economy', 140.00),
(11, 5, 'Economy', 200.00), (12, 5, 'Business', 480.00), (13, 5, 'First', 820.00),
(14, 6, 'Economy', 90.00), (15, 6, 'Business', 250.00),
(16, 7, 'Economy', 85.00), (17, 7, 'Business', 230.00),
(18, 8, 'Economy', 220.00), (19, 8, 'Business', 600.00), (20, 8, 'First', 1000.00),
(21, 9, 'Economy', 250.00), (22, 9, 'Business', 700.00), (23, 9, 'First', 1200.00),
(24, 10, 'Economy', 170.00), (25, 10, 'Business', 430.00), (26, 10, 'First', 780.00),
(27, 11, 'Economy', 260.00), (28, 11, 'Business', 720.00), (29, 11, 'First', 1250.00),
(30, 12, 'Economy', 210.00), (31, 12, 'Business', 500.00), (32, 12, 'First', 850.00),
(33, 13, 'Economy', 130.00),
(34, 14, 'Economy', 95.00), (35, 14, 'Business', 260.00),
(36, 15, 'Economy', 100.00), (37, 15, 'Business', 270.00),
(38, 16, 'Economy', 110.00), (39, 16, 'Business', 290.00), (40, 16, 'First', 500.00),
(41, 17, 'Economy', 145.00), (42, 17, 'Business', 360.00), (43, 17, 'First', 620.00),
(44, 18, 'Economy', 155.00), (45, 18, 'Business', 395.00), (46, 18, 'First', 680.00),
(47, 19, 'Economy', 135.00),
(48, 20, 'Economy', 190.00), (49, 20, 'Business', 470.00), (50, 20, 'First', 810.00),
(51, 21, 'Economy', 88.00), (52, 21, 'Business', 240.00),
(53, 22, 'Economy', 105.00), (54, 22, 'Business', 275.00),
(55, 23, 'Economy', 230.00), (56, 23, 'Business', 610.00), (57, 23, 'First', 1020.00),
(58, 24, 'Economy', 185.00), (59, 24, 'Business', 490.00), (60, 24, 'First', 870.00),
(61, 25, 'Economy', 175.00), (62, 25, 'Business', 440.00), (63, 25, 'First', 790.00),
(64, 26, 'Economy', 195.00), (65, 26, 'Business', 520.00), (66, 26, 'First', 910.00),
(67, 27, 'Economy', 240.00), (68, 27, 'Business', 560.00), (69, 27, 'First', 930.00),
(70, 28, 'Economy', 150.00),
(71, 29, 'Economy', 75.00), (72, 29, 'Business', 210.00),
(73, 30, 'Economy', 92.00), (74, 30, 'Business', 255.00);


SELECT * FROM Airlines;
SELECT * FROM Airports;
SELECT * FROM Flights;
SELECT * FROM TicketPrices;

