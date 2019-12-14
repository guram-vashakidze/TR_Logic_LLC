create database tr_logic collate utf8_general_ci;

create table users
(
  id int auto_increment
    primary key,
  email varchar(200) null,
  pass varchar(500) null,
  firstName varchar(500) null,
  lastName varchar(500) null,
  avatar varchar(500) null,
  phone varchar(20) null,
  bDate varchar(20) null,
  regDate datetime null,
  lastAuth datetime null,
  tempPass varchar(500) null,
  tempPassTime double null
);

create table userAuth
(
  hash varchar(500) not null
    primary key,
  userId int null,
  lastIp int null,
  lastAction datetime null,
  constraint userAuth_users_id_fk
    foreign key (userId) references users (id)
);