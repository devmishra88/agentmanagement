import React from "react";
import { AppHeader } from "../components";

import {
  Typography,
  Container,
  Grid,
  Card,
  CardContent,
  IconButton,
} from "@mui/material";

import { moduleitems } from "../constants";

const Dashboard = () => {
  return (
    <>
      <AppHeader>Dashboard</AppHeader>
      <Container maxWidth="lg">
        <Grid container mt={1} spacing={1}>
          {moduleitems.map((item, index) => (
            <Grid item xs={6} key={index}>
              <Card>
                <CardContent
                  sx={{
                    textAlign: `center`,
                  }}
                >
                  <IconButton
                    sx={{
                      color: item.iconcolor,
                    }}
                  >
                    {item.iconname}
                  </IconButton>
                  <br />
                  <Typography
                    variant="div"
                    sx={{
                      color: item.titlecolor,
                    }}
                  >
                    {item.title}
                  </Typography>
                </CardContent>
              </Card>
            </Grid>
          ))}
        </Grid>
      </Container>
    </>
  );
};

export default Dashboard;
