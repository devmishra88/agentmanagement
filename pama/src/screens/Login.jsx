import React, { useState } from "react";
import Box from "@mui/material/Box";
import Card from "@mui/material/Card";
import CardActions from "@mui/material/CardActions";
import CardContent from "@mui/material/CardContent";
import Button from "@mui/material/Button";
import Typography from "@mui/material/Typography";
import LockOpenIcon from "@mui/icons-material/LockOpen";

import InputLabel from "@mui/material/InputLabel";
import { Divider, TextField } from "@mui/material";
import { Link } from "react-router-dom";

export default function Login() {
  const [mobileNumber, setMobileNumber] = useState("");
  const [hasAlphabet, setHasAlphabet] = useState(false);
  const [password, setPassword] = useState("");
  const [hasError, setHasError] = useState(false);

  // Function to handle input changes
  const handlePasswordChange = (event) => {
    const newPassword = event.target.value;
    setPassword(newPassword);

    // Check if the password is empty
    if (newPassword.trim() === "") {
      setHasError(true);
    } else {
      setHasError(false);
    }
  };

  const handleMobileNumberChange = (e) => {
    const inputValue = e.target.value;
    setMobileNumber(inputValue);

    const hasAlphabetCharacter = /[a-zA-Z]/.test(inputValue);

    // Update the validation state based on the presence of alphabet characters
    setHasAlphabet(hasAlphabetCharacter);
  };

  return (
    <Box
      sx={{
        width: "100%",
        height: "100vh",
        backgroundColor: "#f7f7f7",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
      }}
    >
      <Card
        sx={{
          minWidth: "300px",
          padding: "40px",
          height: "560px",
        }}
      >
        {/* for icons and title */}
        <Box
          sx={{
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            flexDirection: "column",
          }}
        >
          <LockOpenIcon
            sx={{
              fontSize: "70px",
            }}
          />
          <Typography
            sx={{
              fontSize: "2rem",
              fontWeight: "700",
              marginTop: "40px",
            }}
          >
            Agent Login
          </Typography>
        </Box>
        {/*  for form  */}
        <Box
          sx={{
            paddingTop: "35px",
            paddingBottom: "0px",
          }}
        >
          <InputLabel
            sx={{
              textAlign: "left",
            }}
          >
            Mobile
          </InputLabel>
          <TextField
            id="mobileNumber"
            variant="filled"
            size="small"
            type="tel"
            hiddenLabel
            value={mobileNumber}
            onChange={handleMobileNumberChange}
            required
            sx={{
              width: "100%",

              padding: "0px",
            }}
            InputProps={{ disableUnderline: true }}
          ></TextField>

          {hasAlphabet && (
            <>
              <Typography color="red" fontSize="12px" textAlign="left">
                Please Match the requested format
              </Typography>
            </>
          )}

          <Divider sx={{ marginTop: "3px", marginBottom: "3px" }} />

          <InputLabel
            sx={{
              textAlign: "left",
            }}
          >
            Password
          </InputLabel>
          <TextField
            required
            hiddenLabel
            id="password"
            type="password"
            variant="filled"
            size="small"
            value={password}
            onChange={handlePasswordChange}
            error={hasError}
            helperText={hasError ? "Please enter a password" : ""}
            sx={{
              width: "100%",
              border: "none",
            }}
            InputProps={{ disableUnderline: true }}
          ></TextField>
        </Box>
        <Box>
          <Button
            variant="contained"
            sx={{
              margin: "5px",

              fontSize: "0.8rem",
              maxWidth: "90%",
              minWidth: "90%",
              fontWeight: "700",
            }}
          >
            SIGN IN
          </Button>

          <Typography
            style={{
              textDecoration: "none",
            }}
            sx={{
              padding: "40px",
              fontSize: "18px",
              color: "#2196f3",
              textDecoration: "",
            }}
          >
            <Link
              to="/forgot"
              style={{
                textDecoration: "none",
              }}
            >
              Forgot Password?
            </Link>
          </Typography>

          <Typography
            sx={{
              color: "#9e9e9e",
              fontSize: "14px",
            }}
          >
            &copy; 2020 Prem News Agency
          </Typography>
        </Box>
      </Card>
    </Box>
  );
}
