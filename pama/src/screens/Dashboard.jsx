import React from "react";
import {
  Typography,
  Container,
  Grid,
  Card,
  CardContent,
} from "@mui/material";
import Diversity3Icon from "@mui/icons-material/Diversity3";
import AccountCircleIcon from "@mui/icons-material/AccountCircle";
import NewspaperIcon from "@mui/icons-material/Newspaper";
import MapsHomeWorkIcon from "@mui/icons-material/MapsHomeWork";
import InventoryIcon from "@mui/icons-material/Inventory";
import BarChartIcon from "@mui/icons-material/BarChart";
import ReceiptLongIcon from "@mui/icons-material/ReceiptLong";
import ReportIcon from "@mui/icons-material/Report";
import { AppHeader } from "../components";

const Dashboard = () => {
  return (
    <>
      <AppHeader>Dashboard</AppHeader>
      <Container maxWidth="lg">
        <Grid container mt={1} spacing={1}>
          <Grid item xs={6}>
            <Card>
              <CardContent
                sx={{
                  textAlign: `center`,
                }}
              >
                <AccountCircleIcon
                  sx={{
                    color: `#b4c100`,
                  }}
                />
                <br />
                <Typography
                  variant="div"
                  sx={{
                    color: `#007aff`,
                  }}
                >
                  Agency
                </Typography>
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={6}>
            <Card>
              <CardContent
                sx={{
                  textAlign: `center`,
                }}
              >
                <Diversity3Icon
                  sx={{
                    color: `#d32d41`,
                  }}
                />
                <br />
                <Typography
                  variant="div"
                  sx={{
                    color: `#007aff`,
                  }}
                >
                  Agent
                </Typography>
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={6}>
            <Card>
              <CardContent
                sx={{
                  textAlign: `center`,
                }}
              >
                <NewspaperIcon
                  sx={{
                    color: `#4cb5f6`,
                  }}
                />
                <br />
                <Typography
                  variant="div"
                  sx={{
                    color: `#007aff`,
                  }}
                >
                  Newspaper
                </Typography>
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={6}>
            <Card>
              <CardContent
                sx={{
                  textAlign: `center`,
                }}
              >
                <MapsHomeWorkIcon
                  sx={{
                    color: `#b307f7`,
                  }}
                />
                <br />
                <Typography
                  variant="div"
                  sx={{
                    color: `#007aff`,
                  }}
                >
                  Area
                </Typography>
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={6}>
            <Card>
              <CardContent
                sx={{
                  textAlign: `center`,
                }}
              >
                <InventoryIcon
                  sx={{
                    color: `#b4c100`,
                  }}
                />
                <br />
                <Typography
                  variant="div"
                  sx={{
                    color: `#007aff`,
                  }}
                >
                  Purchase
                </Typography>
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={6}>
            <Card>
              <CardContent
                sx={{
                  textAlign: `center`,
                }}
              >
                <BarChartIcon
                  sx={{
                    color: `#d32d41`,
                  }}
                />
                <br />
                <Typography
                  variant="div"
                  sx={{
                    color: `#007aff`,
                  }}
                >
                  Sales
                </Typography>
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={6}>
            <Card>
              <CardContent
                sx={{
                  textAlign: `center`,
                }}
              >
                <ReceiptLongIcon
                  sx={{
                    color: `#b307f7`,
                  }}
                />
                <br />
                <Typography
                  variant="div"
                  sx={{
                    color: `#007aff`,
                  }}
                >
                  Billing
                </Typography>
              </CardContent>
            </Card>
          </Grid>
          <Grid item xs={6}>
            <Card>
              <CardContent
                sx={{
                  textAlign: `center`,
                }}
              >
                <ReportIcon
                  sx={{
                    color: `#4cb5f6`,
                  }}
                />
                <br />
                <Typography
                  variant="div"
                  sx={{
                    color: `#007aff`,
                  }}
                >
                  Report
                </Typography>
              </CardContent>
            </Card>
          </Grid>
          {/* <Grid item xs={6}>
              <Card>
                <CardContent>Logout</CardContent>
              </Card>
            </Grid> */}
        </Grid>
      </Container>
    </>
  );
};

export default Dashboard;
