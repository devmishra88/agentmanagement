import React from "react";
import { AppHeader } from "../components";
import useSwitchRoute from "../hooks/useSwitchRoute";

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
  const switchRoute = useSwitchRoute();

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
                    onClick={() => switchRoute(item.link, false)}
                  >
                    {item.iconname}
                  </IconButton>
                  <br />
                  <IconButton
                    sx={{
                      color: item.titlecolor,
                    }}
                    onClick={() => switchRoute(item.link, false)}
                  >
                    <Typography
                      variant="div"
                      sx={{
                        fontSize: 16,
                      }}
                    >
                      {item.title}
                    </Typography>
                  </IconButton>
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
