DROP DATABASE IF EXISTS OrlandoAirportDB;
CREATE DATABASE OrlandoAirportDB;
USE OrlandoAirportDB;

-- Must be created BEFORE Flights (foreign key dependency)
CREATE TABLE Airlines (
    airline_id INT PRIMARY KEY,
    airline_name VARCHAR(100) NOT NULL
);

-- Must be created BEFORE Flights
CREATE TABLE Airports (
    airport_id INT PRIMARY KEY,
    airport_code VARCHAR(10) NOT NULL,
    airport_name VARCHAR(100)
);

-- Seed at least 1 airport to prevent sync_flights.php from failing
-- (it hardcodes departure_airport_id=1 and arrival_airport_id=1)
INSERT INTO Airports (airport_id, airport_code, airport_name)
VALUES (1, 'MCO', 'Orlando International Airport');

CREATE TABLE Flights (
    flight_id INT AUTO_INCREMENT PRIMARY KEY,
    flight_number VARCHAR(20) UNIQUE,
    airline_id INT NOT NULL,
    departure_airport_id INT NOT NULL,
    arrival_airport_id INT NOT NULL,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    flight_duration INT NOT NULL,
    gate_number VARCHAR(10),
    terminal VARCHAR(10),
    aircraft_type VARCHAR(50),
    is_delayed BOOLEAN,
    status VARCHAR(30),
    passenger_count INT,
    seats_available INT,
    live_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (airline_id) REFERENCES Airlines(airline_id),
    FOREIGN KEY (departure_airport_id) REFERENCES Airports(airport_id),
    FOREIGN KEY (arrival_airport_id) REFERENCES Airports(airport_id)
);

-- Required by flight_details.php
CREATE TABLE TicketPrices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_id INT NOT NULL,
    section_name VARCHAR(50),
    ticket_price DECIMAL(10,2),
    FOREIGN KEY (flight_id) REFERENCES Flights(flight_id)
);
