use project_web;

INSERT INTO User (name, surname, password, email) 
VALUES 
('Natassa', 'Anagnwstopoulou', 'password', 'sec_anagnwstopoulou@ceid.com'),
('Vri', 'Aggeliki', 'password', 'sec_vri@ceid.com'),
('Giannakopoulou', 'Ioanna', 'password', 'sec_giannakopoulou@ceid.com'),
('Dimitropoulou', 'Maria', 'password', 'sec_dimitropoulou@ceid.com');

INSERT INTO secretary (email, mob_tel, land_tel) 
VALUES 
('sec_anagnwstopoulou@ceid.com', 6987654321, 2610996955),
('sec_vri@ceid.com', 6978654321, 2610996940),
('sec_giannakopoulou@ceid.com', 6967654321, 2610996941),
('sec_dimitropoulou@ceid.com', 6958654321, 2610996939);

