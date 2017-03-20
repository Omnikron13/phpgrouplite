CREATE TABLE IF NOT EXISTS groupMembers(
    id      INTEGER PRIMARY KEY,
    userID  INTEGER NOT NULL,
    groupID INTEGER NOT NULL,
    FOREIGN KEY (userID) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (groupID) REFERENCES groups(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE UNIQUE INDEX IF NOT EXISTS groupMembers_index ON groupMembers(
    userID,
    groupID
);
